<?php

/**
 * @author     Jirka Svoboda <code@svobik.com>
 * @copyright  2017 © svobik.com
 * @license    https://www.svobik.com/license.md
 * @timestamp  13/06/2017 09:10
 */

namespace dlds\mlm\kernel\patterns\facades;

use Codeception\Util\Debug;
use dlds\mlm\kernel\exceptions\MlmRewardBuilderError;
use dlds\mlm\kernel\interfaces\MlmParticipantInterface;
use dlds\mlm\kernel\interfaces\MlmSubjectInterface;
use dlds\mlm\kernel\MlmPocketItem;
use dlds\mlm\kernel\patterns\builders\directors\MlmRewardDirector;
use dlds\mlm\kernel\patterns\builders\interfaces\MlmRewardBuilderInterface;
use dlds\mlm\kernel\patterns\builders\MlmRewardBasicBuilder;
use dlds\mlm\kernel\patterns\builders\MlmRewardCustomBuilder;
use dlds\mlm\kernel\patterns\builders\MlmRewardExtraBuilder;
use dlds\mlm\Mlm;
use yii\base\Exception;

/**
 * Class MlmRewardFacade
 * @package dlds\mlm\kernel\patterns\facades
 */
abstract class MlmRewardFacade
{
    /**
     * Generates all allowed rewards for given subject
     * @param MlmSubjectInterface $subject
     * @return bool
     */
    public static function generateAll(MlmSubjectInterface $subject)
    {
        Mlm::pocket()->clear();

        $size = Mlm::pocket()->size();

        foreach (static::procedures() as $procedure) {

            $transaction = \Yii::$app->db->beginTransaction();

            try {

                call_user_func($procedure, $subject);

                $transaction->commit();

            } catch (MlmRewardBuilderError $e) {

                $transaction->rollBack();

                Debug::debug($e->getErrors(true));
                \Yii::error($e->getErrors(true));

            } catch (Exception $e) {

                $transaction->rollBack();

                Debug::debug($e->getMessage());
                \Yii::error($e->getMessage());
            }
        }

        return Mlm::pocket()->size($size);
    }

    /**
     * Creates basic rewards for given subject
     * ---
     * @param MlmSubjectInterface $subject
     * @return bool|integer
     */
    public static function generateBasic(MlmSubjectInterface $subject)
    {
        if (!$subject->__mlmCanRewardByBasic($subject)) {
            return false;
        }

        $builder = MlmRewardBasicBuilder::instance($subject);

        $profiteers = MlmSubjectFacade::profiteersBasic($subject);

        return static::generate($builder, $profiteers);
    }

    /**
     * Creates extra rewards for given subject
     * ---
     * @param MlmSubjectInterface $subject
     * @return bool|integer
     */
    public static function generateExtra(MlmSubjectInterface $subject)
    {
        if (!$subject->__mlmCanRewardByExtra($subject)) {
            return false;
        }

        $builder = MlmRewardExtraBuilder::instance($subject);

        $profiteers = MlmSubjectFacade::profiteersExtra($subject);

        return static::generate($builder, $profiteers);

    }

    /**
     * Creates custom rewards for given subject
     * ---
     * @param MlmSubjectInterface $subject
     * @return bool|integer
     */
    public static function generateCustom(MlmSubjectInterface $subject)
    {
        if (!$subject->__mlmCanRewardByCustom($subject)) {
            return false;
        }

        $builder = MlmRewardCustomBuilder::instance($subject);

        $profiteers = MlmSubjectFacade::profiteersCustom($subject);

        return static::generate($builder, $profiteers);
    }

    /**
     * Process generation
     * @param MlmRewardBuilderInterface $builder
     * @param MlmParticipantInterface[] $profiteers
     * @return bool|integer
     * @throws MlmRewardBuilderError
     */
    protected static function generate(MlmRewardBuilderInterface $builder, array $profiteers)
    {
        if (!$profiteers) {
            return false;
        }

        $pocket = Mlm::pocket();

        $size = $pocket->size();

        $director = MlmRewardDirector::instance();

        foreach ($profiteers as $p) {

            $result = $director->build($builder, $p);

            if (!$result->__mlmValue() && Mlm::cfgSkipWorthless()) {
                \Yii::info(['Reward has been skipped due its worthless', $result->__mlmAttributes()], Mlm::cfgKey());
                continue;
            }

            if (!$result || !$result->__mlmSave()) {
                throw MlmRewardBuilderError::factory($builder, 'Not able to save builder result.');
            }

            \Yii::trace(['Reward has been sucessfully created', $result->__mlmAttributes()], Mlm::cfgKey());

            $pocket->add(new MlmPocketItem($result));
        }

        return $pocket->size($size);
    }

    /**
     * @return array
     */
    protected static function procedures()
    {
        return [
            function ($subject) {
                return static::generateBasic($subject);
            },
            function ($subject) {
                return static::generateExtra($subject);
            },
            function ($subject) {
                return static::generateCustom($subject);
            },
        ];
    }

}

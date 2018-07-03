<?php

/**
 * @author     Jirka Svoboda <code@svobik.com>
 * @copyright  2017 © svobik.com
 * @license    https://www.svobik.com/license.md
 * @timestamp  13/06/2017 09:10
 */

namespace dlds\mlm\kernel\patterns\facades;

use dlds\mlm\kernel\exceptions\MlmRewardBuilderError;
use dlds\mlm\kernel\interfaces\MlmParticipantInterface;
use dlds\mlm\kernel\interfaces\MlmRewardInterface;
use dlds\mlm\kernel\interfaces\MlmSubjectInterface;
use dlds\mlm\kernel\interfaces\queries\MlmRewardQueryInterface;
use dlds\mlm\kernel\MlmPocketItem;
use dlds\mlm\kernel\patterns\builders\directors\MlmRewardDirector;
use dlds\mlm\kernel\patterns\builders\interfaces\MlmRewardBuilderInterface;
use dlds\mlm\kernel\patterns\builders\MlmRewardBasicBuilder;
use dlds\mlm\kernel\patterns\builders\MlmRewardCustomBuilder;
use dlds\mlm\kernel\patterns\builders\MlmRewardExtraBuilder;
use dlds\mlm\Mlm;
use yii\base\Exception;
use yii\helpers\StringHelper;

/**
 * Class MlmRewardFacade
 * @package dlds\mlm\kernel\patterns\facades
 */
abstract class MlmRewardFacade
{
    const GEN_ALL = 0;
    const GEN_CUSTOM = 5;
    const GEN_BASIC = 10;
    const GEN_EXTRA = 20;

    /**
     * Approves all rewards found by given query
     * when it is expecting approval
     * @param MlmRewardQueryInterface $q
     * @return integer
     */
    public static function verifyAll(MlmRewardQueryInterface $q, $delay)
    {
        Mlm::trace($q->createCommand()->rawSql);

        $rewards = $q->all();

        $total = 0;

        /** @var MlmRewardInterface $rwd */
        foreach ($rewards as $rwd) {

            Mlm::trace(sprintf('Verifying %s [%s]', get_class($rwd), $rwd->__mlmPrimaryKey()));

            if ($rwd->__mlmExpectingApproval($delay)) {
                $rwd->__mlmApprove();
            }

            if ($rwd->__mlmExpectingDeny($delay)) {
                $rwd->__mlmDeny();
            }

            if (!$rwd->__mlmApprove()->__mlmSave()) {
                Mlm::trace([
                    sprintf('FAILED verification %s [%s]', get_class($rwd), $rwd->__mlmPrimaryKey()),
                    $rwd->getAttributes(),
                    $rwd->getErrors(),
                ]);
                continue;
            }

            $total += 1;
        }

        return $total;
    }

    /**
     * Generates all allowed rewards for given subject
     * @param MlmSubjectInterface $subject
     * @return integer
     */
    public static function generateAll(MlmSubjectInterface $subject)
    {
        Mlm::trace(sprintf('[GENERATE ALL] for %s [%s] at %s', StringHelper::basename(get_class($subject)),
            $subject->__mlmPrimaryKey(), time()), true);

        Mlm::pocket()->clear();

        $size = Mlm::pocket()->size();

        foreach (static::generatorProcedures() as $procedure) {

            $transaction = \Yii::$app->db->beginTransaction();

            try {

                call_user_func($procedure, $subject);

                $transaction->commit();

            } catch (MlmRewardBuilderError $e) {

                $transaction->rollBack();

                Mlm::trace($e->getErrors(true));
                \Yii::error($e->getErrors(true));

            } catch (Exception $e) {

                $transaction->rollBack();

                Mlm::trace($e->getMessage());
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
        if (!$subject->__mlmCanRewardByBasic()) {

            Mlm::trace(sprintf('[GENERATE BASIC] was PREVENTED for [%s] at %s', $subject->__mlmPrimaryKey(), time()));

            return false;
        }

        Mlm::trace(sprintf('[GENERATE BASIC] was STARTED for [%s] at %s', $subject->__mlmPrimaryKey(), time()));

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
        if (!$subject->__mlmCanRewardByExtra()) {

            Mlm::trace(sprintf('[GENERATE EXTRA] was PREVENTED for [%s] at %s', $subject->__mlmPrimaryKey(), time()));

            return false;
        }

        Mlm::trace(sprintf('[GENERATE EXTRA] was STARTED for [%s] at %s', $subject->__mlmPrimaryKey(), time()));

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
        if (!$subject->__mlmCanRewardByCustom()) {

            Mlm::trace(sprintf('[GENERATE CUSTOM] was PREVENTED for [%s] at %s', $subject->__mlmPrimaryKey(), time()));

            return false;
        }

        Mlm::trace(sprintf('[GENERATE CUSTOM] was STARTED for [%s] at %s', $subject->__mlmPrimaryKey(), time()));

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
                Mlm::trace(['[GENERATE] was SKIPPED', $result->__mlmAttributes()]);
                continue;
            }

            if (!$result || !$result->__mlmSave()) {
                Mlm::trace(['[GENERATE] was FAILED', $result->__mlmAttributes()]);
                throw MlmRewardBuilderError::factory($builder, 'Not able to save builder result.');
            }

            Mlm::trace(['[GENERATE] was SUCCESSFULL', $result->__mlmAttributes()]);

            $pocket->add(new MlmPocketItem($result));
        }

        return $pocket->size($size);
    }

    /**
     * @return array
     */
    protected static function generatorProcedures()
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

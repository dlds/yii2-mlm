<?php

/**
 * @link http://www.digitaldeals.cz/
 * @copyright Copyright (c) 2014 Digital Deals s.r.o.
 * @license http://www.digitaldeals.cz/license/
 */

namespace dlds\mlm;

use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;
use dlds\mlm\components\interfaces\MlmParticipantInterface;
use dlds\mlm\components\interfaces\MlmSubjectInterface;
use dlds\mlm\components\interfaces\MlmRewardInterface;

/**
 * This is the main class of the dlds\mlm component that should be registered as an application component.
 *
 * @author Jiri Svoboda <jiri.svobodao@dlds.cz>
 * @package mlm
 */
class Mlm extends \yii\base\Component
{

    /**
     * Rewards statuses
     */
    const RW_STATUS_PENDING = 'pending';
    const RW_STATUS_APPROVED = 'approved';
    const RW_STATUS_DENIED = 'denied';

    /**
     * @var array
     */
    public $rules = [];

    /**
     * @var float maximal allowed rules %
     */
    public $limitRules = 100;

    /**
     * @var int approce/deny delay in seconds
     */
    public $delayPending = 3600;

    /**
     * @var string participant class
     */
    public $clsParticipant;

    /**
     * @var string reward class
     */
    public $clsRewardBasic;

    /**
     * @var string investment reward class
     */
    public $clsRewardExtra;

    /**
     * @var array registered rewards sources classes
     */
    public $clsSubjects = [];

    /**
     * @var bool
     */
    public $isActive = true;

    /**
     * @var string
     */
    public $useKey;

    /**
     * @var string
     */
    protected static $id = 'mlm';

    /**
     * @inheritdoc
     */
    public function init()
    {
        // validates class that earns rewards & class that generates rewards
        $this->validateClsParticipant();
        $this->validateClsSubject();

        // validates rewards classes
        $this->validateClsRewardBasic();
        $this->validateClsRewardExtra();

        // validates rewards generation rules
        $this->validateRules();

        if ($this->useKey) {
            static::$id = $this->useKey;
        }
    }

    /**
     * @return Mlm
     */
    public static function instance()
    {
        return \Yii::$app->get(static::$id);
    }

    /**
     * Autorun
     */
    public function autorun()
    {
        // 1. approve pending commissions
        // 2. create investment commissions
        // 3. withdraw locked commisison
        die('MLM autorun done');
    }

    /**
     * Calculates reward amount based on given source amount and level
     * @param $source
     * @param $lvl
     * @return float
     */
    public function calc($source, $lvl)
    {
        $rule = ArrayHelper::getValue($this->rules, $lvl, 0);

        if (!$rule) {
            return 0;
        }

        return $source * ($rule / 100);
    }

    /**
     * @param MlmSubjectInterface $subject
     */
    public function reward(MlmSubjectInterface $subject)
    {
        if (!$this->isActive) {
            return false;
        }

        if ($subject->mlmCanRewardByBasic($subject)) {
            $this->rewardBasic($subject);
        }

        if ($subject->mlmCanRewardByCustom($subject)) {
            $this->rewardCustom($subject);
        }
    }


    // <editor-fold defaultstate="collapsed" desc="Mlm Reward Methods">
    public function rewardBasic(MlmSubjectInterface $subject)
    {
        $owner = $subject->mlmSubjectOwner();

        if (!$owner) {
            return false;
        }

        $ancestors = $owner->mlmAllAncestors($this->maxRuleLvl());

        $transaction = \Yii::$app->db->beginTransaction();

        foreach ($ancestors as $ansr) {

            $lvl = $owner->mlmLvl() - $ansr->mlmLvl();

            $rwd = new $this->clsRewardBasic;

            $rwd->mlmSetOwner($ansr);
            $rwd->mlmSetSubject($subject);

            $value = $this->ruleValue($lvl, $ansr->mlmIsMainParticipant());

            $rwd->mlmSetValue($value);

            $rwd->mlmSetLevel($lvl);

            if (!$rwd->mlmCreate()) {
                $transaction->rollBack();
                return false;
            }
        }

        $transaction->commit();
        return true;
    }

    public function rewardCustom()
    {

    }
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Mlm Rules Methods">
    /**
     * Retrieves rule value
     * ---
     * If all following is true than sum of all following rules until max rule and current rule is retrieved
     * ---
     * @param int $lvl
     * @param boolean $allFollowing
     * @return float
     */
    public function ruleValue($lvl, $allFollowing = false)
    {
        if ($allFollowing) {

            $val = 0;

            for ($i = $lvl; $i <= $this->maxRuleLvl(); $i++) {
                $val += $this->ruleValue($lvl, false);
            }

            return $val;
        }

        return ArrayHelper::getValue($this->rules, $lvl, 0);
    }

    /**
     * Retrieves maximum rule level
     * @return int
     */
    public function maxRuleLvl()
    {
        return max(array_keys($this->rules));
    }
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Mlm Config Valiators">
    /**
     * Checks if participant class is set and has propper features
     * @throws \yii\base\Exception
     * @return boolean
     */
    protected function validateClsParticipant()
    {
        if (!$this->clsParticipant) {
            throw new \yii\base\Exception('Participant class must be set.');
        }

        $object = \Yii::createObject($this->clsParticipant);

        if (!$object instanceof MlmParticipantInterface) {
            throw new \yii\base\Exception(sprintf('Participant class has to implement %s', StringHelper::basename(MlmParticipantInterface::class)));
        }
    }

    /**
     * Checks if reward basic class is set and has propper features
     * @throws \yii\base\Exception
     * @return boolean
     */
    protected function validateClsRewardBasic()
    {
        if (!$this->clsRewardBasic) {
            throw new \yii\base\Exception('Reward Basic class must be set.');
        }

        $object = \Yii::createObject($this->clsRewardBasic);

        if (!$object instanceof MlmRewardInterface) {
            throw new \yii\base\Exception(sprintf('Reward Basic class has to implement %s', StringHelper::basename(MlmRewardInterface::class)));
        }
    }

    /**
     * Checks if reward extra class is set and has propper features
     * @throws \yii\base\Exception
     * @return boolean
     */
    protected function validateClsRewardExtra()
    {
        if (!$this->clsRewardExtra) {
            throw new \yii\base\Exception('Participant class must be set.');
        }

        $object = \Yii::createObject($this->clsRewardExtra);

        if (!$object instanceof MlmRewardInterface) {
            throw new \yii\base\Exception(sprintf('Participant class has to implement %s', StringHelper::basename(MlmRewardInterface::class)));
        }
    }

    /**
     * Checks if subject class is set and has propper features
     * @throws \yii\base\Exception
     * @return boolean
     */
    protected function validateClsSubject()
    {
        if (!$this->clsSubjects) {
            throw new \yii\base\Exception('Subject classes must be set.');
        }

        foreach ($this->clsSubjects as $subject) {

            $object = \Yii::createObject($subject);

            if (!$object instanceof MlmSubjectInterface) {
                throw new \yii\base\Exception(sprintf('Subject class has to implement %s', StringHelper::basename(MlmSubjectInterface::class)));
            }
        }

    }

    /**
     * Checks if reward rules are set properly
     * @throws \yii\base\Exception
     * @return boolean
     */
    protected function validateRules()
    {
        if ($this->limitRules < array_sum($this->rules)) {
            throw new \yii\base\Exception('Rules overflow.');
        }

        ksort($this->rules);

        for ($i = 1; $i < count($this->rules); $i++) {

            if (!ArrayHelper::keyExists($i, $this->rules)) {
                throw new \yii\base\Exception(sprintf('Rule lvl %s missing.', $i));
            }
        }

        return true;
    }

    // </editor-fold>

}

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
use dlds\mlm\components\interfaces\rewards\MlmRewardBasicInterface;
use dlds\mlm\components\interfaces\rewards\MlmRewardExtraInterface;

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
    public $clsSubject = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        // validates class that earns rewards & class that generates rewards
        //$this->validateClsParticipant();
        //$this->validateClsSubject();

        // validates rewards classes
        $this->validateClsRewardBasic();
        $this->validateClsRewardExtra();

        // validates rewards generation rules
        $this->validateRules();
    }

    /**
     *
     */
    public function autorun()
    {
        // 1. approve pending commissions
        // 2. create investment commissions
        // 3. withdraw locked commisison
        die('MLM autorun done');
    }

    public function calcReward($amount, $line)
    {
        $rule = ArrayHelper::getValue($this->rules, $line, 0);

        if (!$rule) {
            return 0;
        }

        return $amount * ($rule / 100);
    }

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

        if (!$object instanceof MlmRewardBasicInterface) {
            throw new \yii\base\Exception(sprintf('Reward Basic class has to implement %s', StringHelper::basename(MlmRewardBasicInterface::class)));
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

        if (!$object instanceof MlmRewardExtraInterface) {
            throw new \yii\base\Exception(sprintf('Participant class has to implement %s', StringHelper::basename(MlmRewardExtraInterface::class)));
        }
    }

    /**
     * Checks if subject class is set and has propper features
     * @throws \yii\base\Exception
     * @return boolean
     */
    protected function validateClsSubject()
    {
        if (!$this->clsSubject) {
            throw new \yii\base\Exception('Subject class must be set.');
        }

        $object = \Yii::createObject($this->clsSubject);

        if (!$object instanceof MlmSubjectInterface) {
            throw new \yii\base\Exception(sprintf('Subject class has to implement %s', StringHelper::basename(MlmSubjectInterface::class)));
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

}

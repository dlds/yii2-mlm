<?php

/**
 * @link http://www.digitaldeals.cz/
 * @copyright Copyright (c) 2014 Digital Deals s.r.o.
 * @license http://www.digitaldeals.cz/license/
 */

namespace dlds\mlm;

use Codeception\Util\Debug;
use dlds\mlm\helpers\MlmValueHelper;
use dlds\mlm\kernel\interfaces\MlmParticipantInterface;
use dlds\mlm\kernel\interfaces\MlmRewardInterface;
use dlds\mlm\kernel\interfaces\MlmSubjectInterface;
use dlds\mlm\kernel\interfaces\queries\MlmParticipantQueryInterface;
use dlds\mlm\kernel\interfaces\queries\MlmRewardQueryInterface;
use dlds\mlm\kernel\interfaces\queries\MlmSubjectQueryInterface;
use dlds\mlm\kernel\MlmPocket;
use dlds\mlm\kernel\patterns\facades\MlmParticipantFacade;
use dlds\mlm\kernel\patterns\facades\MlmRewardFacade;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;

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
     * Value rounding
     */
    const MLM_ROUND_UP = 'mlm_round_up';
    const MLM_ROUND_DOWN = 'mlm_round_down';

    /**
     * @var array
     */
    public $rules = [];

    /**
     * @var float maximal allowed rules %
     */
    public $limitRules = 100;

    /**
     * @var int approve/deny delay in seconds
     */
    public $delayPending = 3600;

    /**
     * @var int decimal numbers rounding precision
     */
    public $roundPrecision = 4;

    /**
     * MLM values rounding mode
     * ---
     * Special mode Mlm::MLM_ROUND_DOWN or Mlm::MLM_ROUND_UP can be used
     * or default php round() method modes.
     * ---
     * @see http://php.net/manual/en/function.round.php
     * ---
     * @var int decimal numbers rounding mode
     */
    public $roundMode = self::MLM_ROUND_DOWN;

    /**
     * @var bool indicates if rewards with value = 0 should be skipped
     */
    public $skipWorthlessRewards = true;

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
     * @var string custom reward class
     */
    public $clsRewardCustom;

    /**
     * @var array registered rewards sources classes
     */
    public $clsSubjects = [];

    /**
     * Status aliases
     * @var array
     */
    public $alsStatuses = [];

    /**
     * @var bool
     */
    public $isCreatingActive = true;

    /**
     * @var bool
     */
    public $isVerifyingActive = true;

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
        $this->inspect();

        if ($this->useKey) {
            static::$id = $this->useKey;
        }
    }

    /**
     * Inspects module configuration
     */
    public function inspect()
    {
        // validates class that earns rewards & class that generates rewards
        $this->validateClsParticipant();
        $this->validateClsSubject();

        // validates rewards classes
        $this->validateClsRewardBasic();
        $this->validateClsRewardExtra();
        $this->validateClsRewardCustom();

        // validates rewards generation rules
        $this->validateRules();
    }

    /**
     * @return Mlm
     */
    public static function instance()
    {
        return \Yii::$app->get(static::$id);
    }

    /**
     * Automatically runs regularly actions (cron shortcut)
     * @param integer $limit
     * @return array
     */
    public function autorun($limit = 10)
    {
        $result = [0, 0];

        // 1. verify rewards
        foreach (static::clsRewards() as $cls) {
            static::trace(sprintf('[AUTORUN] VERIFY %s', StringHelper::basename($cls)), '=== === ===');
            $result[0] += $this->verifyMultipleRewards(call_user_func([$cls, 'find']), $limit);
        }

        // 2. create rewards
        foreach ($this->clsSubjects as $cls) {
            static::trace(sprintf('[AUTORUN] CREATE rewards for %s', StringHelper::basename($cls)), '=== === ===');
            $result[1] += $this->createMultipleRewards(call_user_func([$cls, 'find']), $limit);
        }

        return $result;
    }

    // <editor-fold defaultstate="collapsed" desc="Mlm Reward Methods">

    /**
     * Runs verifying of all rewards assigned to given subject
     * @param MlmSubjectInterface $subject
     * @return integer|bool
     */
    public function verifyRewards(MlmSubjectInterface $subject)
    {
        if (!$this->isVerifyingActive) {
            return false;
        }

        $total = 0;

        foreach (static::clsRewards() as $cls) {

            /** @var MlmRewardQueryInterface $query */
            $query = call_user_func([$cls, 'find']);
            $query->__mlmSource($subject->__mlmPrimaryKey(), $subject->__mlmType());

            $total += $this->verifyMultipleRewards($query, false);
        }

        return $total;
    }

    /**
     * Runs verification of rewards based on given query
     * @param MlmRewardQueryInterface $query
     * @param integer $limit
     * @return integer
     */
    public function verifyMultipleRewards(MlmRewardQueryInterface $query, $limit = 10)
    {
        if (!$this->isVerifyingActive) {
            return false;
        }

        $total = 0;

        if ($limit) {
            $query->limit($limit);
        }

        $toApprove = clone $query;
        $toDeny = clone $query;

        $toApprove->__mlmExpectingApproval(static::delay());
        $toDeny->__mlmExpectingDeny(static::delay());

        static::trace(sprintf('[VERIFY MULTIPLE] %s of %s for APPROVE', $toApprove->count(), StringHelper::basename(get_class($query))), '---');
        $total += MlmRewardFacade::approveAll($toApprove, static::delay());

        static::trace(sprintf('[VERIFY MULTIPLE] %s of %s for DENY', $toDeny->count(), StringHelper::basename(get_class($query))), '---');
        $total += MlmRewardFacade::denyAll($toDeny, static::delay());

        return $total;
    }

    /**
     * Runs generating of all types of rewards for single subject
     * @param MlmSubjectInterface $subject
     * @return integer
     */
    public function createRewards(MlmSubjectInterface $subject)
    {
        if (!$this->isCreatingActive) {
            return false;
        }

        return MlmRewardFacade::generateAll($subject);
    }

    /**
     * Runs generating of all types of rewards for multiple subjects
     * @param MlmSubjectQueryInterface $query
     * @param integer $limit
     * @return integer
     */
    public function createMultipleRewards(MlmSubjectQueryInterface $query, $limit = 10)
    {
        if (!$this->isCreatingActive) {
            return false;
        }

        if ($limit) {
            $query->limit($limit);
        }

        $subjects = $query->__mlmExpectingRewards()->all();

        static::trace(sprintf('[CREATE MULTIPLE] %s of %s for REWARD', count($subjects), StringHelper::basename(get_class($query))), '===');

        if (!$subjects) {
            return false;
        }

        $total = 0;

        foreach ($subjects as $sbj) {
            $total += $this->createRewards($sbj);
        }

        return $total;
    }

    /**
     * Calculates value from given amount and percentile
     * ---
     * Uses MLM::round() method to round decimals
     * ---
     * @see Mlm::round()
     * ---
     * @param $amount
     * @param $percentile
     * @return float
     */
    public static function calc($amount, $percentile)
    {
        return static::round($amount * $percentile);
    }

    /**
     * Makes MLM value rounding based on module configuration
     * @param double $value
     * @return double
     */
    public static function round($value)
    {
        $instance = static::instance();

        if (self::MLM_ROUND_DOWN === $instance->roundMode) {

            return MlmValueHelper::roundDown($value, $instance->roundPrecision);
        }

        if (self::MLM_ROUND_UP === $instance->roundMode) {

            return MlmValueHelper::roundUp($value, $instance->roundPrecision);
        }

        return round($value, $instance->roundPrecision, $instance->roundMode);
    }

    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Mlm Proxies">
    /**
     * Retrieves mlm rules
     * @return array
     */
    public static function rules()
    {
        return static::instance()->rules;
    }

    /**
     * Retrieves approve/deny delay
     * @return int
     */
    public static function delay()
    {
        return static::instance()->delayPending;
    }

    /**
     * Retrieves reward status alias
     * ---
     * Given status will be retrieved when status alias is not found.
     * ---
     * @param $status
     * @return mixed
     */
    public static function alsStatus($status)
    {
        $alias = ArrayHelper::getValue(static::instance()->alsStatuses, $status);

        return $alias ? $alias : $status;
    }

    /**
     * Retrieves participant class name
     * @return string
     */
    public static function clsParticipant()
    {
        return static::instance()->clsParticipant;
    }

    /**
     * Retrieves subject class name
     * @param string $key
     * @return string
     */
    public static function clsSubject($key)
    {
        return ArrayHelper::getValue(static::instance()->clsSubjects, $key);
    }

    /**
     * Retrieves classes of all rewards kinds
     * @return array
     */
    public static function clsRewards()
    {
        return [
            static::clsRewardBasic(),
            static::clsRewardExtra(),
            static::clsRewardCustom(),
        ];
    }

    /**
     * Retrieves basic reward class name
     * @return string
     */
    public static function clsRewardBasic()
    {
        return static::instance()->clsRewardBasic;
    }

    /**
     * Retrieves extra reward class name
     * @return string
     */
    public static function clsRewardExtra()
    {
        return static::instance()->clsRewardExtra;
    }

    /**
     * Retrieves custom reward class name
     * @return string
     */
    public static function clsRewardCustom()
    {
        return static::instance()->clsRewardCustom;
    }

    /**
     * Retrieves MLM module identification
     * @return string
     */
    public static function cfgKey()
    {
        return static::$id;
    }

    /**
     * Indicates if worthless rewards should be skipped
     * @return bool
     */
    public static function cfgSkipWorthless()
    {
        return static::instance()->skipWorthlessRewards;
    }

    /**
     * Retrieves mlm root
     * @return MlmParticipantInterface
     */
    public static function root()
    {
        return MlmParticipantFacade::findMain();
    }

    /**
     * Retrieves mlm participant
     * @param int $pk primary key
     * @return MlmParticipantInterface
     */
    public static function participant($pk)
    {
        return MlmParticipantFacade::findOne($pk);
    }

    /**
     * Retreives mlm pocket
     * @return MlmPocket
     */
    public static function pocket()
    {
        return MlmPocket::instance();
    }

    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Mlm Config Validators">

    /**
     * Checks if participant class is set and has propper features
     * @throws Exception
     * @return boolean
     */
    protected function validateClsParticipant()
    {
        if (!$this->clsParticipant) {
            throw new Exception('Participant class must be set.');
        }

        $rfl1 = new \ReflectionClass($this->clsParticipant);

        if (!$rfl1->implementsInterface(MlmParticipantInterface::class)) {
            throw new Exception(sprintf('Participant class has to implement %s', StringHelper::basename(MlmParticipantInterface::class)));
        }

        $query = call_user_func([$this->clsParticipant, 'find']);

        $rfl2 = new \ReflectionClass($query);

        if (!$rfl2->implementsInterface(MlmParticipantQueryInterface::class)) {
            throw new Exception(sprintf('Participant class has to implement %s', StringHelper::basename(MlmParticipantQueryInterface::class)));
        }

    }

    /**
     * Checks if reward basic class is set and has all features
     * @throws Exception
     * @return boolean
     */
    protected function validateClsRewardBasic()
    {
        if (!$this->clsRewardBasic) {
            throw new Exception('Reward Basic class must be set.');
        }

        $rfl = new \ReflectionClass($this->clsRewardBasic);

        if (!$rfl->implementsInterface(MlmRewardInterface::class)) {
            throw new Exception(sprintf('Reward Basic class has to implement %s', StringHelper::basename(MlmRewardInterface::class)));
        }

        $query = call_user_func([$this->clsRewardBasic, 'find']);

        $rfl2 = new \ReflectionClass($query);

        if (!$rfl2->implementsInterface(MlmRewardQueryInterface::class)) {
            throw new Exception(sprintf('Reward Basic Query class has to implement %s', StringHelper::basename(MlmRewardQueryInterface::class)));
        }
    }

    /**
     * Checks if reward extra class is set and has all features
     * @throws Exception
     * @return boolean
     */
    protected function validateClsRewardExtra()
    {
        if (!$this->clsRewardExtra) {
            throw new Exception('Reward Extra class must be set.');
        }

        $rfl1 = new \ReflectionClass($this->clsRewardExtra);

        if (!$rfl1->implementsInterface(MlmRewardInterface::class)) {
            throw new Exception(sprintf('Reward Extra class has to implement %s', StringHelper::basename(MlmRewardInterface::class)));
        }

        $query = call_user_func([$this->clsRewardExtra, 'find']);

        $rfl2 = new \ReflectionClass($query);

        if (!$rfl2->implementsInterface(MlmRewardQueryInterface::class)) {
            throw new Exception(sprintf('Reward Extra Query class has to implement %s', StringHelper::basename(MlmRewardQueryInterface::class)));
        }
    }

    /**
     * Checks if reward custom class is set and has all features
     * @throws Exception
     * @return boolean
     */
    protected function validateClsRewardCustom()
    {
        if (!$this->clsRewardCustom) {
            throw new Exception('RewardCustom class must be set.');
        }

        $rfl1 = new \ReflectionClass($this->clsRewardCustom);

        if (!$rfl1->implementsInterface(MlmRewardInterface::class)) {
            throw new Exception(sprintf('Reward Custom class has to implement %s', StringHelper::basename(MlmRewardInterface::class)));
        }

        $query = call_user_func([$this->clsRewardCustom, 'find']);

        $rfl2 = new \ReflectionClass($query);

        if (!$rfl2->implementsInterface(MlmRewardQueryInterface::class)) {
            throw new Exception(sprintf('Reward Custom Query class has to implement %s', StringHelper::basename(MlmRewardQueryInterface::class)));
        }
    }

    /**
     * Checks if subject class is set and has propper features
     * @throws Exception
     * @return boolean
     */
    protected function validateClsSubject()
    {
        if (!$this->clsSubjects) {
            throw new Exception('Subject classes must be set.');
        }

        foreach ($this->clsSubjects as $subject) {

            $rfl1 = new \ReflectionClass($subject);

            if (!$rfl1->implementsInterface(MlmSubjectInterface::class)) {
                throw new Exception(sprintf('Subject class has to implement %s', StringHelper::basename(MlmSubjectInterface::class)));
            }

            $query = call_user_func([$subject, 'find']);

            $rfl2 = new \ReflectionClass($query);

            if (!$rfl2->implementsInterface(MlmSubjectQueryInterface::class)) {
                throw new Exception(sprintf('Subject Query class has to implement %s', StringHelper::basename(MlmSubjectQueryInterface::class)));
            }
        }
    }

    /**
     * Checks if reward rules are set properly
     * @throws Exception
     * @return boolean
     */
    protected function validateRules()
    {
        if ($this->limitRules < array_sum($this->rules)) {
            throw new Exception('Rules overflow.');
        }

        ksort($this->rules);

        for ($i = 1; $i < count($this->rules); $i++) {

            if (!ArrayHelper::keyExists($i, $this->rules)) {
                throw new Exception(sprintf('Rule lvl %s missing.', $i));
            }
        }

        return true;
    }

    // </editor-fold>

    /**
     * Trace and debug given message
     * @param string $message
     * @param boolean|string $separator
     * @return boolean
     */
    public static function trace($message, $separator = false)
    {
        /*
        if ($separator) {
            var_dump((true === $separator) ? '---' : $separator);
        }

        var_dump($message);
        */

        if (YII_DEBUG) {
            if ($separator) {
                Debug::debug((true === $separator) ? '---' : $separator);
            }

            Debug::debug($message);
            return true;
        }
    }

}

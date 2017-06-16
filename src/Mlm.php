<?php

/**
 * @link http://www.digitaldeals.cz/
 * @copyright Copyright (c) 2014 Digital Deals s.r.o.
 * @license http://www.digitaldeals.cz/license/
 */

namespace dlds\mlm;

use Codeception\Util\Debug;
use dlds\mlm\helpers\MlmSubjectFacade;
use dlds\mlm\helpers\MlmValueHelper;
use dlds\mlm\kernel\interfaces\MlmParticipantInterface;
use dlds\mlm\kernel\interfaces\MlmRewardInterface;
use dlds\mlm\kernel\interfaces\MlmSubjectInterface;
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
     * @var int approce/deny delay in seconds
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
     * Automatically runs regularly actions (cron shortcutł)
     */
    public function autorun()
    {
        // 1. approve pending commissions
        // 2. create investment commissions
        // 3. withdraw locked commisison
    }

    // <editor-fold defaultstate="collapsed" desc="Mlm Reward Methods">

    /**
     * Runs generating of all types of rewards
     * @param MlmSubjectInterface $subject
     * @return bool
     */
    public function verifyRewards(MlmSubjectInterface $subject)
    {
        if (!$this->isActive) {
            return false;
        }

        return MlmRewardFacade::generateAll($subject);
    }

    /**
     * Runs generating of all types of rewards
     * @param MlmSubjectInterface $subject
     * @return bool
     */
    public function createRewards(MlmSubjectInterface $subject)
    {
        if (!$this->isActive) {
            return false;
        }

        return MlmRewardFacade::generateAll($subject);
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
        Debug::debug($this->clsParticipant);
        if (!$this->clsParticipant) {
            throw new Exception('Participant class must be set.');
        }

        $rfl = new \ReflectionClass($this->clsParticipant);

        if (!$rfl->implementsInterface(MlmParticipantInterface::class)) {
            throw new Exception(sprintf('Participant class has to implement %s', StringHelper::basename(MlmParticipantInterface::class)));
        }
    }

    /**
     * Checks if reward basic class is set and has propper features
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
    }

    /**
     * Checks if reward extra class is set and has propper features
     * @throws Exception
     * @return boolean
     */
    protected function validateClsRewardExtra()
    {
        if (!$this->clsRewardExtra) {
            throw new Exception('Participant class must be set.');
        }

        $rfl = new \ReflectionClass($this->clsRewardExtra);

        if (!$rfl->implementsInterface(MlmRewardInterface::class)) {
            throw new Exception(sprintf('Participant class has to implement %s', StringHelper::basename(MlmRewardInterface::class)));
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

            $rfl = new \ReflectionClass($subject);

            if (!$rfl->implementsInterface(MlmSubjectInterface::class)) {
                throw new Exception(sprintf('Subject class has to implement %s', StringHelper::basename(MlmSubjectInterface::class)));
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

}

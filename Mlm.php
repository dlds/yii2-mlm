<?php
/**
 * @link http://www.digitaldeals.cz/
 * @copyright Copyright (c) 2014 Digital Deals s.r.o.
 * @license http://www.digitaldeals.cz/license/
 */

namespace dlds\mlm;

use yii\helpers\ArrayHelper;
use dlds\mlm\interfaces\MlmParticipantInterface;
use dlds\mlm\interfaces\MlmCommissionInterface;
use dlds\mlm\interfaces\MlmCommissionSourceInterface;
use dlds\mlm\interfaces\MlmCommissionsQueryInterface;

/**
 * This is the main class of the dlds\mlm component that should be registered as an application component.
 *
 * @author Jiri Svoboda <jiri.svobodao@dlds.cz>
 * @package mlm
 */
class Mlm extends \yii\base\Component {

    /**
     * Commissions types
     */
    const COMMISSION_TYPE_DIRECT = 0;
    const COMMISSION_TYPE_CUSTOM = 5;
    const COMMISSION_TYPE_TREE = 10;
    const COMMISSION_TYPE_TREE_NOT_ASSIGNED = 15;
    const COMMISSION_TYPE_BETA = 20;
    const COMMISSION_TYPE_ALPHA = 30;
    const COMMISSION_TYPE_UNDIVIDED = 100;

    /**
     * Commissions statuses
     */
    const COMMISSION_STATUS_PENDING = 0;
    const COMMISSION_STATUS_APPROVED = 10;
    const COMMISSION_STATUS_LOCKED = 15;
    const COMMISSION_STATUS_REQUESTED = 20;
    const COMMISSION_STATUS_PAID = 30;

    /**
     * Generator results
     */
    const RESULT_UNKNOWN = 0;
    const RESULT_SUCCESS = 100;
    const RESULT_SUCCESS_PARTIAL = 101;
    const RESULT_WARNING = 200;
    const RESULT_WARNING_UNDIVIDED = 201;
    const RESULT_ERROR = 300;
    const RESULT_ERROR_OVERFLOW = 301;

    /**
     * Total commissions amount
     */
    const TOTAL_AMOUNT = 100;

    /**
     * @var array defines tree commissions percentage means that by theese rules
     * commission will be spread throug mlm tree structure
     * Each rule has to be specified as "tree level" => "commission"
     */
    public $treeCommissionsRules = [
        1 => 25,
        6 => 5,
        7 => 1.03,
        8 => 0.77,
    ];

    /**
     * @var int Indicates defualt tree depth limit commissions will affect (this level is reached everytime)
     */
    public $treeTresholdBasicRule = 6;

    /**
     * @var array defines direct commissions percentage means commission which would not be
     * spread in mlm tree will be spread among participant by theese rules
     * Each rule has to be specified as "participant_id" => "commission"
     */
    public $directCommissionsRules = [
        1 => self::TOTAL_AMOUNT,
    ];

    /**
     * @var array holds commissions which should not be generated
     */
    public $disabledCommissionsRules = [];

    /**
     * @var int max decimal places of commisssoin value
     */
    public $commissionPrecisionRule = 3;

    /**
     * @var int max decimal places of commisssoin value
     */
    public $keepHistory = false;

    /**
     * @var string main participant
     */
    public $participantClass;

    /**
     * @var boolean indicates if generating results will be logged in DB,
     * "logTable" id required for this feature
     */
    public $dbLogging = false;

    /**
     * @var boolean indicates if generating results will be logged in DB
     */
    public $logTable = false;

    /**
     * @var boolean indicates if email notification will be send on generator error,
     * "notifiedEmail" is required for this feature
     */
    public $errorsNotifications = false;

    /**
     * @var string notified email when error occures
     */
    public $notifiedEmail = false;

    /**
     * @var boolean indicates if commissions which can't be assigned (user is
     * not eligible to take it) will be kept or spread to investors
     */
    public $keepNotAssignedCommission = false;

    /**
     * @var interfaces\MlmCommissionsHolderInterface current commissions holder
     */
    protected $commissionsHolder;

    /**
     * @var MlmParticipantInterface main participant (used for unspreaded commissions and to spread investors commissions)
     */
    protected $mainParticipant;

    /**
     * @var components\MlmFormatter current mlm formatter
     */
    protected $formatter;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->mainParticipant = $this->getMainParticipant();

        ksort($this->treeCommissionsRules);

        if (!$this->validateTreeTresholdsRules())
        {
            throw new \yii\base\Exception('Tree treshold basic rule is greater than max level set in treeCommissionsRules');
        }

        if (!$this->validateTreeCommissionsRules())
        {
            throw new \yii\base\Exception('Tree level commissions rules are invalid. Sum is greater than 100%');
        }

        if (!$this->validateDirectCommissionsRules())
        {
            throw new \yii\base\Exception('Direct commissions rules are invalid. Sum is not equals 100%');
        }

        if (!$this->validateNotificationsConfig())
        {
            throw new \yii\base\Exception('Notifications config is invalid "notifiedEmail" is required when this feature is enabled');
        }

        if (!$this->validateLoggingConfig())
        {
            throw new \yii\base\Exception('Logging config is invalid "logTable" is required when this feature is enabled');
        }

        if ($this->commissionPrecisionRule < 0)
        {
            throw new \yii\base\Exception('Commission precission rule must greater or equals 0');
        }
    }

    /**
     * Retrieves MLM formatter instance
     * @return components\MlmFormatter current formatter
     */
    public function getFormatter()
    {
        if (!$this->formatter)
        {
            $this->formatter = new components\MlmFormatter();
        }

        return $this->formatter;
    }

    /**
     * Retrieves main participant based on module config "mainParticipantId"
     * @return mixed main participant identity or null if does not exists
     */
    public function getMainParticipant()
    {
        if (!$this->mainParticipant)
        {
            $object = \Yii::createObject($this->participantClass);

            if (!$object instanceof MlmParticipantInterface)
            {
                throw new \yii\base\Exception('Mlm Participant Class has to implement MlmParticipantInterface');
            }

            $this->mainParticipant = $object::getMainParticipant();
        }

        return $this->mainParticipant;
    }

    /**
     * Try to set participant's locked commissions as approved
     * @param MlmParticipantInterface $participant given participant requesting commissions
     * @param MlmCommissionInterface $modelCommission commission model
     */
    public function approveCommissions(MlmCommissionsQueryInterface $query)
    {
        // find only commisisons with status PENDING
        $query->hasStatus(Mlm::COMMISSION_STATUS_PENDING);

        // set all found commissions as approved
        return handlers\MlmCommissionHandler::updateAll($query, Mlm::COMMISSION_STATUS_APPROVED);
    }

    /**
     * Try to set participant's locked commissions as unlocked
     * @param MlmParticipantInterface $participant given participant requesting commissions
     * @param MlmCommissionInterface $modelCommission commission model
     */
    public function unlockCommissions(MlmParticipantInterface $participant, MlmCommissionInterface $modelCommission)
    {
        // put participant & commission model to be processed by handler
        // update method which tries to set locked commissions as unlocked
        return handlers\MlmCommissionHandler::update($participant, $modelCommission, Mlm::COMMISSION_STATUS_LOCKED, Mlm::COMMISSION_STATUS_APPROVED);
    }

    /**
     * Try to set participant's approved commissions as locked
     * @param MlmParticipantInterface $participant given participant requesting commissions
     * @param MlmCommissionInterface $modelCommission commission model
     */
    public function lockCommissions(MlmParticipantInterface $participant, MlmCommissionInterface $modelCommission)
    {
        // put participant & commission model to be processed by handler
        // update method which tries to set approved commissions as locked
        return handlers\MlmCommissionHandler::update($participant, $modelCommission, Mlm::COMMISSION_STATUS_APPROVED, Mlm::COMMISSION_STATUS_LOCKED);
    }

    /**
     * Try to set participant's locked commissions as requested
     * @param MlmParticipantInterface $participant given participant requesting commissions
     * @param MlmCommissionInterface $modelCommission commission model
     */
    public function requestCommissions(MlmParticipantInterface $participant, MlmCommissionInterface $modelCommission)
    {
        // put participant & commission model to be processed by handler
        // update method which tries to set locked commissions as requested
        return handlers\MlmCommissionHandler::update($participant, $modelCommission, Mlm::COMMISSION_STATUS_LOCKED, Mlm::COMMISSION_STATUS_REQUESTED);
    }

    /**
     * Run commissions generator based on module setting
     * @param \dlds\mlm\interfaces\MlmCommissionSourceInterface $source given source commission will be generated from
     * @param \dlds\mlm\interfaces\MlmCommissionInterface $model given commission model which will be populated
     */
    public function generateCommissions(MlmCommissionSourceInterface $source, MlmCommissionInterface $model)
    {
        // get participant of given source
        $participant = $source->getParticipant();

        // if participant does implements appropriate interface throw exception
        if (!$participant instanceof MlmParticipantInterface)
        {
            throw new Exception('Mlm Participant must be instance of MlmParticipantInterface');
        }

        // chech if commissions can be generated by checking thath on given source
        if ($source->canCreateCommissions())
        {
            // create new commissions holder which will hold all generated commission
            $this->commissionsHolder = new holders\BasicCommissionsHolder;

            // check if given commission type is disabled and should not be generated
            if (!$this->isCommissionDisabled(self::COMMISSION_TYPE_CUSTOM))
            {
                // create commissions through appropriate handler and add it to current holder
                $this->commissionsHolder->addCommissions(handlers\MlmCustomCommissionHandler::create($source, $model), self::COMMISSION_TYPE_CUSTOM);
            }

            // check if given commission type is disabled and should not be generated
            if (!$this->isCommissionDisabled(self::COMMISSION_TYPE_DIRECT))
            {
                // create commissions through appropriate handler and add it to current holder
                $this->commissionsHolder->addCommissions(handlers\MlmDirectCommissionHandler::create($source, $model), self::COMMISSION_TYPE_DIRECT);
            }

            // check if given commission type is disabled and should not be generated
            if (!$this->isCommissionDisabled(self::COMMISSION_TYPE_TREE))
            {
                // create commissions through appropriate handler and add it to current holder
                $this->commissionsHolder->addCommissions(handlers\MlmTreeCommissionHandler::create($source, $model), self::COMMISSION_TYPE_TREE);
            }

            // check if given commission type is disabled and should not be generated
            if (!$this->isCommissionDisabled(self::COMMISSION_TYPE_BETA))
            {
                // create commissions through appropriate handler and add it to current holder
                $this->commissionsHolder->addCommissions(handlers\MlmBetaCommissionHandler::create($source, $model), self::COMMISSION_TYPE_BETA);

                // substracts generated betas commissions sum from main participant tree commission
                $this->substractBetasFromMainParticipant();
            }

            // check if given commission type is disabled and should not be generated
            if (!$this->isCommissionDisabled(self::COMMISSION_TYPE_ALPHA))
            {
                // create commissions through appropriate handler and add it to current holder
                $this->commissionsHolder->addCommissions(handlers\MlmAlphaCommissionHandler::create($source, $model), self::COMMISSION_TYPE_ALPHA);
            }

            // create commissions through appropriate handler and add it to current holder
            $this->commissionsHolder->addCommissions(handlers\MlmUndividedCommissionHandler::create($source, $model), self::COMMISSION_TYPE_UNDIVIDED);

            // put current commissions holder to be processed by handler - all commissions held in holder will be saved
            $result = handlers\MlmCommissionHandler::enroll($this->commissionsHolder);

            // clear current commissions holder
            $this->commissionsHolder->clear();

            // check if given commission type is disabled and should not be generated
            if ($this->keepHistory && !$this->isCommissionDisabled(self::COMMISSION_TYPE_TREE))
            {
                // create commissions through appropriate handler and add it to current holder
                $this->commissionsHolder->addCommissions(handlers\MlmComissionHistoryHandler::create($source, $source->getHistoryModel()), self::COMMISSION_TYPE_TREE);

                handlers\MlmComissionHistoryHandler::clear($source);

                return $result && handlers\MlmCommissionHandler::enroll($this->commissionsHolder);
            }

            return $result;
        }
    }

    /**
     * Retrieves direct commissions rules sum as percent value
     * @return float commissions sum
     */
    public function getDirectCommissionsRulesSum()
    {
        // reset rules sum holder
        $sum = 0;

        // go through all rules
        foreach ($this->directCommissionsRules as $value)
        {
            // add rule value to sum holder
            $sum += $value;
        }

        // return sum holder value
        return $sum;
    }

    /**
     * Retrieves tree commissions rules sum as percent value
     * @return float commissions sum
     */
    public function getTreeCommissionsRulesSum()
    {
        // reset sum holder and reached level holder
        $reachedLevels = $sum = 0;

        // go through all rules - structured as "maximal level rule will be applied" => "rule value"
        foreach ($this->treeCommissionsRules as $maxLevel => $value)
        {
            // create multiplier for value based on currently reached level and "rule maximal level"
            $multiplier = $maxLevel - $reachedLevels;

            // multiply "rule value" by created multiplier and add to sum holder
            $sum += ($multiplier * $value);

            // set reached level to current "rule maximal level"
            $reachedLevels = $maxLevel;
        }

        // retun sum holder value
        return $sum;
    }

    /**
     * Retrieves commision percents for given level based on module config rules
     * @param int $level
     * @return float commission percentage
     */
    public function getTreeCommissionRuleAmount($level, MlmCommissionSourceInterface $source = null)
    {
        $amount = 0;

        if ($level > 0)
        {
            foreach ($this->treeCommissionsRules as $maxlevel => $rule)
            {
                if ($amount === 0 && $level <= $maxlevel)
                {
                    $amount = $rule;
                    break;
                }
            }
        }

        if ($source)
        {
            $customRulesSum = $source->getCustomCommissionsRulesSum();

            // if custom rules are defined
            if ($customRulesSum)
            {
                // remove custom rules amount from TOTAL (100%) amount and calculate direct commission amount of that
                // exmpl.: total 100% - custom 90% = available 10% => available 10% * (participant rule = 25%) = 2.5%
                return $this->calculateCommissionValue(self::TOTAL_AMOUNT - $customRulesSum, $amount);
            }
        }

        return $amount;
    }

    /**
     * Retrieves max levels of commissions from module config
     * @return int max treshold
     */
    public function getTreeCommissionRuleMaxLevel()
    {
        $levels = array_keys($this->treeCommissionsRules);
        return array_pop($levels);
    }

    /**
     * Retrieves direct commission participants based on module config
     * @return array all direct commission participants in array
     */
    public function getDirectCommissionParticipants()
    {
        $stack = [];

        foreach ($this->directCommissionsRules as $uid => $commission)
        {
            $participant = \Yii::createObject($this->participantClass)->findOne($uid);

            if ($participant)
            {
                $stack[$uid] = $participant;
            }
        }

        return $stack;
    }

    /**
     * Retrieves custom commission participants based on module config
     * @return array all direct commission participants in array
     */
    public function getCustomCommissionParticipants(MlmCommissionSourceInterface $source = null)
    {
        $stack = [];

        foreach ($source->getCustomCommissionsRules() as $uid => $commission)
        {
            $participant = \Yii::createObject($this->participantClass)->findOne($uid);

            if ($participant)
            {
                $stack[$uid] = $participant;
            }
        }

        return $stack;
    }

    /**
     * Retrieves current undivided commission amount
     * @return float undivided commission amount
     */
    public function getUndividedCommissionAvailableToSpread()
    {
        return (self::TOTAL_AMOUNT - $this->commissionsHolder->getSum());
    }

    /**
     * Retrieves available direct commission to spread means rest of percent
     * after substracting tree level commission percents
     * @return type
     */
    public function getDirectCommissionAvailableToSpread(MlmCommissionSourceInterface $source = null)
    {
        if ($source)
        {
            $customRulesSum = $source->getCustomCommissionsRulesSum();

            // if custom rules are defined
            if ($customRulesSum)
            {
                // remove custom rules amount from TOTAL (100%) amount and calculate direct commission amount of that
                // exmpl.: total 100% - custom 90% = available 10% => available 10% * (direct rule - tree rules sum = 50%) = 5%
                return $this->calculateCommissionValue(self::TOTAL_AMOUNT - $customRulesSum, (self::TOTAL_AMOUNT - $this->getTreeCommissionsRulesSum()));
            }
        }

        return self::TOTAL_AMOUNT - $this->getTreeCommissionsRulesSum();
    }

    /**
     * Retrieves main participant current tree commission held in commission holder
     * @return mixed participant's commission or FALSE if not exist
     */
    public function getAlphaCommissionAvailableToSpread()
    {
        return $this->getUndividedCommissionAvailableToSpread();
    }

    /**
     * Retrieves main participant current tree commission held in commission holder
     * @return mixed participant's commission or FALSE if not exist
     */
    public function getBetaCommissionAvailableToSpread()
    {
        $commissions = $this->getHeldTreeCommissions();

        if ($commissions)
        {
            return ArrayHelper::getValue($commissions, $this->getMainParticipant()->primaryKey);
        }

        return false;
    }

    /**
     * Retrieves seniors
     * @param MlmParticipantInterface $participant
     */
    public function getDirectSeniors(MlmParticipantInterface $participant)
    {
        return $participant->getQuerySeniors(1)->one();
    }

    /**
     * Retrieves juniors
     * @param MlmParticipantInterface $participant
     */
    public function getJuniors(MlmParticipantInterface $participant, $depth = null)
    {
        return $participant->getQueryJuniors($depth)->all();
    }

    /**
     * Retrieves seniors
     * @param MlmParticipantInterface $participant
     */
    public function getSeniors(MlmParticipantInterface $participant, $depth = null)
    {
        return $participant->getQuerySeniors($depth)->all();
    }

    /**
     * Retrieves juniors
     * @param MlmParticipantInterface $participant
     */
    public function getJuniorsCount(MlmParticipantInterface $participant, $depth = null)
    {
        return $participant->getQueryJuniors($depth)->count();
    }

    /**
     * Retrieves seniors
     * @param MlmParticipantInterface $participant
     */
    public function getSeniorsCount(MlmParticipantInterface $participant, $depth = null)
    {
        return $participant->getQuerySeniors($depth)->count();
    }

    /**
     * Retrieves commission value
     * @param interfaces\MlmCommissionInterface $commission given commission AR
     * @param boolean $incVat indicates if retrieved commission will be returned with vat
     * @return float commission value
     */
    public function getCommissionValue(interfaces\MlmCommissionInterface $commission, $incVat = true)
    {
        return $this->calculateCommissionValue($commission->getSource()->getAmountToSpread($incVat), $commission->getAmount());
    }

    /**
     * Retrieves commission percenatage
     * @param MlmParticipantInterface $participant given participant
     * @param int $level given level
     * @param float $amount given amount
     * @return int
     */
    public function getParticipantTreeCommissionAmount(MlmParticipantInterface $participant, $level, MlmCommissionSourceInterface $source = null)
    {
        if ($participant->canTakeTreeCommission($level))
        {
            return $this->getTreeCommissionRuleAmount($level, $source);
        }

        return 0;
    }

    /**
     * Retrieves direct commission percentage for given participant
     * @param MlmParticipantInterface $participant given participant
     * @return float commission percentage
     */
    public function getParticipantDirectCommissionAmount(MlmParticipantInterface $participant, MlmCommissionSourceInterface $source)
    {
        $available = $this->getDirectCommissionAvailableToSpread($source);

        return $this->calculateCommissionValue($available, ArrayHelper::getValue($this->directCommissionsRules, $participant->primaryKey, 0));
    }

    /**
     * Retrieves custom commission percentage for given participant based on given source
     * @param MlmParticipantInterface $participant given participant
     * @return float commission percentage
     */
    public function getParticipantCustomCommissionAmount(MlmParticipantInterface $participant, MlmCommissionSourceInterface $source)
    {
        return $source->getCustomCommissionRuleAmount($participant);
    }

    /**
     * Retrieves personal commission percentage of amount for given participant
     * @param MlmParticipantInterface $participant given participant
     * @param float $amount given amount to be spread
     * @return float commission percentage
     */
    public function getParticipantPersonalCommissionAmount(MlmParticipantInterface $participant, $amount)
    {
        return $this->calculateCommissionValue($amount, $participant->getPersonalCommission());
    }

    /**
     * Retrieves participant maximal possible tree commission value
     * @param MlmParticipantInterface $participant
     * @param float $defaultFee default fee
     * @return boolean $incVat indicates if vat will be included
     */
    public function getParticipantMaxTreeProfit(MlmParticipantInterface $participant, $defaultFee, $incVat = true)
    {
        return $this->calculateTreeProfit($participant, $defaultFee, $incVat, true);
    }

    /**
     * Retrieves participant available tree commission profit
     * @param MlmParticipantInterface $participant
     * @param float $defaultFee default fee
     * @return boolean $incVat indicates if vat will be included
     */
    public function getParticipantAvailableTreeProfit(MlmParticipantInterface $participant, $defaultFee, $incVat = true)
    {
        return $this->calculateTreeProfit($participant, $defaultFee, $incVat, false);
    }

    /**
     * Retrieves currently held direct commissions in commission holder
     * @return array held direct commissions
     */
    public function getHeldDirectCommissions()
    {
        return $this->commissionsHolder->getCommissions(self::COMMISSION_TYPE_DIRECT);
    }

    /**
     * Retrieves currently held custom commissions in commission holder
     * @return array held direct commissions
     */
    public function getHeldCustomCommissions()
    {
        return $this->commissionsHolder->getCommissions(self::COMMISSION_TYPE_CUSTOM);
    }

    /**
     * Retrieves currently held tree commissions in commission holder
     * @return array held tree commissions
     */
    public function getHeldTreeCommissions()
    {
        return $this->commissionsHolder->getCommissions(self::COMMISSION_TYPE_TREE);
    }

    /**
     * Retrieves currently held alpha commissions in commission holder
     * @return array held alpha commissions
     */
    public function getHeldAlphaCommissions()
    {
        return $this->commissionsHolder->getCommissions(self::COMMISSION_TYPE_ALPHA);
    }

    /**
     * Retrieves held beta commissions in commission holder
     * @return array held beta commissions
     */
    public function getHeldBetaCommissions()
    {
        return $this->commissionsHolder->getCommissions(self::COMMISSION_TYPE_BETA);
    }

    /**
     * Retrieves result message
     * @param int $result
     */
    public function getResultMessage($result)
    {
        switch ($result)
        {
            case self::RESULT_SUCCESS:
                return \Yii::t('dlds/mlm', '[Mlm generator] SUCCESS');
            case self::RESULT_SUCCESS_PARTIAL:
                return \Yii::t('dlds/mlm', '[Mlm generator] PARTIAL SUCCESS');
            case self::RESULT_WARNING:
                return \Yii::t('dlds/mlm', '[Mlm generator] WARNING');
            case self::RESULT_WARNING_UNDIVIDED:
                return \Yii::t('dlds/mlm', '[Mlm generator] WARNING UNDIVIDED');
            case self::RESULT_ERROR:
                return \Yii::t('dlds/mlm', '[Mlm generator] ERROR');
            case self::RESULT_ERROR_OVERFLOW:
                return \Yii::t('dlds/mlm', '[Mlm generator] ERROR OVERFLOW');
            default:
                return \Yii::t('dlds/mlm', '[Mlm generator] UNKNOW RESULT');
        }
    }

    /**
     * Calculates commission value based on given amounts
     * @param float $sourceAmount source amount the commission will be calculated from
     * @param float $commissionAmount percents of commission to be used in calculation
     * @return float commission value
     */
    public function calculateCommissionValue($sourceAmount, $commissionAmount)
    {
        return $this->refineCommissionValue($sourceAmount * $commissionAmount / 100);
    }

    /**
     * Return all possible commissions types
     */
    public static function getCommissionsTypes()
    {
        return [
            self::COMMISSION_TYPE_DIRECT => \Yii::t('dlds/mlm', 'Direct commission'),
            self::COMMISSION_TYPE_CUSTOM => \Yii::t('dlds/mlm', 'Custom commission'),
            self::COMMISSION_TYPE_TREE => \Yii::t('dlds/mlm', 'Tree commission'),
            self::COMMISSION_TYPE_BETA => \Yii::t('dlds/mlm', 'Beta commission'),
            self::COMMISSION_TYPE_ALPHA => \Yii::t('dlds/mlm', 'Alpha commission'),
            self::COMMISSION_TYPE_UNDIVIDED => \Yii::t('dlds/mlm', 'Undivied commission'),
        ];
    }

    /**
     * Return all possible commissions types
     */
    public static function getCommissionsStatuses()
    {
        return [
            self::COMMISSION_STATUS_PENDING => \Yii::t('dlds/mlm', 'Pending'),
            self::COMMISSION_STATUS_APPROVED => \Yii::t('dlds/mlm', 'Approved'),
            self::COMMISSION_STATUS_LOCKED => \Yii::t('dlds/mlm', 'Ready to pay'),
            self::COMMISSION_STATUS_REQUESTED => \Yii::t('dlds/mlm', 'Requested'),
            self::COMMISSION_STATUS_PAID => \Yii::t('dlds/mlm', 'Paid'),
        ];
    }

    /**
     * Substract current betas commissions from main participant tree commisssion
     */
    protected function substractBetasFromMainParticipant()
    {
        $betas = $this->commissionsHolder->getSum(self::COMMISSION_TYPE_BETA);

        $commission = $this->commissionsHolder->getCommission(self::COMMISSION_TYPE_TREE, $this->getMainParticipant()->primaryKey);

        if ($commission)
        {
            $commission->setAmount($commission->getAmount() - $betas);

            $this->commissionsHolder->updateCommission(self::COMMISSION_TYPE_TREE, $this->getMainParticipant()->primaryKey, $commission);
        }
    }

    /**
     * Indicates if given commission type is disabled for generating
     * @param int $type given commission type
     * @return boolean TRUE if is disabled otherwise FALSE
     */
    protected function isCommissionDisabled($type)
    {
        return in_array($type, $this->disabledCommissionsRules);
    }

    /**
     * Checks if direct commissions rules are set properly
     * Walks through all direct commissions and check if they are are ok
     * @return boolean TRUE if commissions are ok, otherwise FALSE
     */
    protected function validateDirectCommissionsRules()
    {
        return ($this->getDirectCommissionsRulesSum() == self::TOTAL_AMOUNT);
    }

    /**
     * Checks if tree commissions rules are set properly
     * Walks through all commissions rules and check if they are ok
     * @return boolean TRUE if commissions are ok, otherwise FALSE
     */
    protected function validateTreeCommissionsRules()
    {
        return ($this->getTreeCommissionsRulesSum() <= self::TOTAL_AMOUNT);
    }

    /**
     * Checks if tree tesholds rules are set properly
     * @return boolean TRUE if tresholds are ok, otherwise FALSE
     */
    protected function validateTreeTresholdsRules()
    {

        return !($this->treeTresholdBasicRule > $this->getTreeCommissionRuleMaxLevel());
    }

    /**
     * Checks if appropriate configs are set when notifications are enabled
     * @return boolean TRUE on valid otherwise FALSE
     */
    protected function validateNotificationsConfig()
    {
        return !$this->errorsNotifications || $this->notifiedEmail;
    }

    /**
     * Checks if appropriate configs are set when logging is enabled
     * @return boolean TRUE on valid otherwise FALSE
     */
    protected function validateLoggingConfig()
    {
        return !$this->dbLogging || $this->logTable;
    }

    /**
     * Retrieves participant tree profit
     * @param MlmParticipantInterface $participant
     * @param float $defaultFee default fee
     * @param boolean $incVat indicates if vat will be included
     * @param boolean $maxProfit indicates if maximal profit will be calculated
     * or only currently available profit means unpaid profit
     * @return float tree profit
     */
    private function calculateTreeProfit(MlmParticipantInterface $participant, $defaultFee, $incVat = true, $maxProfit = true)
    {
        $profit = 0;

        if ($participant->canTakeTreeCommission($this->getTreeCommissionRuleMaxLevel()))
        {
            $juniors = $participant->getQueryJuniors($this->getTreeCommissionRuleMaxLevel(), true)->all();
        }
        else
        {
            $juniors = $participant->getQueryJuniors($this->treeTresholdBasicRule, true)->all();
        }

        $participantDepth = $participant->getTreeDepth();

        foreach ($juniors as $junior)
        {
            if ($maxProfit || !$junior->hasFeePaid())
            {
                $level = $junior->getTreeDepth() - $participantDepth;

                $juniorPersonalFee = $junior->getPersonalFee($incVat);

                if ($juniorPersonalFee)
                {
                    $profit += $this->calculateCommissionValue($juniorPersonalFee, $this->getParticipantTreeCommissionAmount($participant, $level));
                }
                else
                {
                    $profit += $this->calculateCommissionValue($defaultFee, $this->getParticipantTreeCommissionAmount($participant, $level));
                }
            }
        }

        return $profit;
    }

    /**
     * Refines commission value
     * @param float $value given value
     * @return float refined value
     */
    private function refineCommissionValue($value)
    {
        $pointPos = intval(strpos($value, '.'));

        if ($pointPos === 0)
        {
            return $value;
        }

        return floatval(substr($value, 0, $pointPos + $this->commissionPrecisionRule + 1));
    }
}
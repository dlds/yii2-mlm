<?php
/**
 * @link http://www.digitaldeals.cz/
 * @copyright Copyright (c) 2014 Digital Deals s.r.o.
 * @license http://www.digitaldeals.cz/license/
 */

namespace dlds\mlm;

use yii\helpers\ArrayHelper;
use dlds\mlm\interfaces\MlmParticipantInterface;

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
    const COMMISSION_DIRECT = 0;
    const COMMISSION_TREE = 10;
    const COMMISSION_BETA = 20;
    const COMMISSION_ALPHA = 30;
    const COMMISSION_UNDIVIDED = 100;

    /**
     * Results
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
     * @var string main participant
     */
    public $participantClass;

    /**
     * @var int main participant id (used for unspreaded commissions and to spread investors commissions)
     */
    public $mainParticipantId;

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
     * @var interfaces\MlmCommissionsHolderInterface current commissions holder
     */
    protected $commissionsHolder;

    /**
     * @inheritdoc
     */
    public function init()
    {
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
     * Retrieves main participant based on module config "mainParticipantId"
     * @return mixed main participant identity or null if does not exists
     */
    public function getMainParticipant()
    {
        try
        {
            $object = \Yii::createObject($this->participantClass);

            return $object->findOne($this->mainParticipantId);
        }
        catch (Exception $ex)
        {
            return null;
        }
    }

    /**
     * Run commissions generator based on module setting
     * @param \dlds\mlm\interfaces\MlmCommissionSourceInterface $source given source commission will be generated from
     * @param \dlds\mlm\interfaces\MlmCommissionInterface $model given commission model which will be populated
     */
    public function generateCommissions(interfaces\MlmCommissionSourceInterface $source, interfaces\MlmCommissionInterface $model)
    {
        // get participant of given source
        $participant = $source->getParticipant();

        // if participant does implements appropriate interface throw exception
        if (!$participant instanceof interfaces\MlmParticipantInterface)
        {
            throw new Exception('Mlm Participant must be instance of MlmParticipantInterface');
        }

        // chech if commissions can be generated by checking thath on given source
        if ($source->canCreateCommissions())
        {
            // create new commissions holder which will hold all generated commission
            $this->commissionsHolder = new holders\BasicCommissionsHolder();

            // check if given commission type is disabled and should not be generated
            if (!$this->isCommissionDisabled(self::COMMISSION_DIRECT))
            {
                // create commissions through appropriate handler and add it to current holder
                $this->commissionsHolder->addCommissions(handlers\MlmDirectCommissionHandler::create($source, $model), self::COMMISSION_DIRECT);
            }

            // check if given commission type is disabled and should not be generated
            if (!$this->isCommissionDisabled(self::COMMISSION_TREE))
            {
                // create commissions through appropriate handler and add it to current holder
                $this->commissionsHolder->addCommissions(handlers\MlmTreeCommissionHandler::create($source, $model), self::COMMISSION_TREE);
            }

            // check if given commission type is disabled and should not be generated
            if (!$this->isCommissionDisabled(self::COMMISSION_BETA))
            {
                // create commissions through appropriate handler and add it to current holder
                $this->commissionsHolder->addCommissions(handlers\MlmBetaCommissionHandler::create($source, $model), self::COMMISSION_BETA);

                // substracts generated betas commissions sum from main participant tree commission
                $this->substractBetasFromMainParticipant();
            }

            // check if given commission type is disabled and should not be generated
            if (!$this->isCommissionDisabled(self::COMMISSION_ALPHA))
            {
                // create commissions through appropriate handler and add it to current holder
                $this->commissionsHolder->addCommissions(handlers\MlmAlphaCommissionHandler::create($source, $model), self::COMMISSION_ALPHA);
            }

            // create commissions through appropriate handler and add it to current holder
            $this->commissionsHolder->addCommissions(handlers\MlmUndividedCommissionHandler::create($source, $model), self::COMMISSION_UNDIVIDED);


            handlers\MlmCommissionHandler::process($this->commissionsHolder);

            $this->commissionsHolder->clear();
        }
    }

    /**
     * Retrieves direct commissions rules sum as percent value
     * @return float commissions sum
     */
    public function getDirectCommissionsRulesSum()
    {
        $sum = 0;

        foreach ($this->directCommissionsRules as $value)
        {
            $sum += $value;
        }

        return $sum;
    }

    /**
     * Retrieves tree commissions rules sum as percent value
     * @return float commissions sum
     */
    public function getTreeCommissionsRulesSum()
    {
        $reachedLevels = $sum = 0;

        foreach ($this->treeCommissionsRules as $maxLevel => $value)
        {
            $multiplier = $maxLevel - $reachedLevels;

            $sum += ($multiplier * $value);

            $reachedLevels = $maxLevel;
        }

        return $sum;
    }

    /**
     * Retrieves commision percents for given level based on module config rules
     * @param int $level
     * @return float commission percentage
     */
    public function getTreeCommissionRuleAmount($level)
    {
        if ($level > 0)
        {
            foreach ($this->treeCommissionsRules as $maxlevel => $amount)
            {
                if ($level <= $maxlevel)
                {
                    return $amount;
                }
            }
        }

        return 0;
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
    public function getDirectCommissionAvailableToSpread()
    {
        return (self::TOTAL_AMOUNT - $this->getTreeCommissionsRulesSum());
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
            return ArrayHelper::getValue($commissions, $this->mainParticipantId);
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
     * @param MlmParticipantInterface $participant given participant
     * @param int $level given level
     * @param float $amount given amount
     * @return int
     */
    public function getCommissionValue(interfaces\MlmCommissionInterface $commission)
    {
        return $this->calculateCommissionValue($commission->getSource()->getAmount(), $commission->getAmount());
    }

    /**
     * Retrieves commission percenatage
     * @param MlmParticipantInterface $participant given participant
     * @param int $level given level
     * @param float $amount given amount
     * @return int
     */
    public function getParticipantTreeCommissionAmount(MlmParticipantInterface $participant, $level)
    {
        if ($participant->canTakeTreeCommission($level))
        {
            return $this->getTreeCommissionRuleAmount($level);
        }

        return 0;
    }

    /**
     * Retrieves direct commission percentage for given participant
     * @param MlmParticipantInterface $participant given participant
     * @return float commission percentage
     */
    public function getParticipantDirectCommissionAmount(MlmParticipantInterface $participant)
    {
        $available = $this->getDirectCommissionAvailableToSpread();

        return $this->calculateCommissionValue($available, ArrayHelper::getValue($this->directCommissionsRules, $participant->primaryKey, 0));
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
     * Retrieves maximal possible tree commission value
     * @param MlmParticipantInterface $participant
     * @param float $defaultFee default fee
     * @return float possible commission value sum
     */
    public function getParticipantMaxTreeProfit(MlmParticipantInterface $participant, $defaultFee)
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
            $level = $junior->getTreeDepth() - $participantDepth;

            $juniorPersonalFee = $junior->getPersonalFee();

            if ($juniorPersonalFee)
            {
                $profit += $this->getCommissionValue($participant, $level, $juniorPersonalFee);
            }
            else
            {
                $profit += $this->getCommissionValue($participant, $level, $defaultFee);
            }
        }

        return $profit;
    }

    /**
     * Retrieves currently held direct commissions in commission holder
     * @return array held direct commissions
     */
    public function getHeldDirectCommissions()
    {
        return $this->commissionsHolder->getCommissions(self::COMMISSION_DIRECT);
    }

    /**
     * Retrieves currently held tree commissions in commission holder
     * @return array held tree commissions
     */
    public function getHeldTreeCommissions()
    {
        return $this->commissionsHolder->getCommissions(self::COMMISSION_TREE);
    }

    /**
     * Retrieves currently held alpha commissions in commission holder
     * @return array held alpha commissions
     */
    public function getHeldAlphaCommissions()
    {
        return $this->commissionsHolder->getCommissions(self::COMMISSION_ALPHA);
    }

    /**
     * Retrieves held beta commissions in commission holder
     * @return array held beta commissions
     */
    public function getHeldBetaCommissions()
    {
        return $this->commissionsHolder->getCommissions(self::COMMISSION_BETA);
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
     * Return all possible commissions types
     */
    public static function getCommissionsTypes()
    {
        return [
            self::COMMISSION_DIRECT => \Yii::t('dlds/mlm', 'Direct commission'),
            self::COMMISSION_TREE => \Yii::t('dlds/mlm', 'Tree commission'),
            self::COMMISSION_BETA => \Yii::t('dlds/mlm', 'Beta commission'),
            self::COMMISSION_ALPHA => \Yii::t('dlds/mlm', 'Alpha commission'),
            self::COMMISSION_UNDIVIDED => \Yii::t('dlds/mlm', 'Undivied commission'),
        ];
    }

    /**
     * Substract current betas commissions from main participant tree commisssion
     */
    protected function substractBetasFromMainParticipant()
    {
        $betas = $this->commissionsHolder->getSum(self::COMMISSION_BETA);

        $commission = $this->commissionsHolder->getCommission(self::COMMISSION_TREE, $this->mainParticipantId);

        if ($commission)
        {
            $commission->setAmount($commission->getAmount() - $betas);

            $this->commissionsHolder->updateCommission(self::COMMISSION_TREE, $this->mainParticipantId, $commission);
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
     * Calculates commission value based on given amounts
     * @param float $sourceAmount source amount the commission will be calculated from
     * @param float $commissionAmount percents of commission to be used in calculation
     * @return float commission value
     */
    private function calculateCommissionValue($sourceAmount, $commissionAmount)
    {
        return $this->refineCommissionValue($sourceAmount * $commissionAmount / 100);
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
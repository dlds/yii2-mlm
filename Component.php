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
class Component extends \yii\base\Component {
    /**
     * @var int Indicates defualt tree depth limit commissions will affect (this level is reached everytime)
     */
    public $tresholdBasic = 6;

    /**
     * @var array defines level commissions percentage
     */
    public $levelCommissions = [
        1 => 25,
        6 => 5,
        7 => 1.03,
        8 => 0.77,
    ];

    /**
     * @var int max decimal places of commisssoin value
     */
    public $commissionPrecision = 3;

    /**
     * 1.line juniors treshold for 6-10 level seniors commission
     */
    //const TREE_EXTRA_COMMISSION_TRESHOLD = 20;

    /**
     * Direct commissions
     */
    //const DIRECT_COMMISSION_VALUE = 47.42;

    /**
     * Minimal commission value to issue
     */
    //const MINIMAL_COMMISSION_VALUE_TO_ISSUE = 1000;

    /**
     * @inheritdoc
     */
    public function init()
    {
        ksort($this->levelCommissions);

        if (!$this->checkLevelsTresholds())
        {
            throw new \yii\base\Exception('Level treshold basic is greater than max level set in levelCommissions');
        }

        if (!$this->checkLevelsCommissions())
        {
            throw new \yii\base\Exception('Level commissions config is invalid. Sum is over 100%');
        }

        if ($this->commissionPrecision < 0)
        {
            throw new \yii\base\Exception('Commission precission must greater or equals 0');
        }
    }

    /**
     * Retrieves commision percents based on given level
     * @param int $level
     * @return float commission percentage
     */
    public function getLevelCommission($level)
    {
        if ($level > 0)
        {
            foreach ($this->levelCommissions as $maxlevel => $percentage)
            {
                if ($level <= $maxlevel)
                {
                    return $percentage;
                }
            }
        }

        return 0;
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
     * Retrieves commission percenatage
     * @param MlmParticipantInterface $participant given participant
     * @param int $level given level
     * @param float $amount given amount
     * @return int
     */
    public function getParticipantCommission(MlmParticipantInterface $participant, $level)
    {
        if ($participant->canTakeCommission($level))
        {
            return $this->getLevelCommission($level);
        }

        return 0;
    }

    public function getParticipantCommissionValue(MlmParticipantInterface $participant, $level, $amount)
    {
        $commission = $this->getParticipantCommission($participant, $level);

        return $this->refineCommissionValue($amount * $commission / 100);
    }

    /**
     * Retrieves maximal possible tree commission value
     * @param MlmParticipantInterface $participant
     * @param float $amount input amount
     * @return float possible commission value sum
     */
    public function getParticipantMaxTreeProfit(MlmParticipantInterface $participant, $amount)
    {
        $profit = 0;

        if ($participant->canTakeCommission($this->levelMax()))
        {
            $juniors = $participant->getQueryJuniors($this->levelMax(), true)->all();
        }
        else
        {
            $juniors = $participant->getQueryJuniors($this->tresholdBasic, true)->all();
        }

        $participantDepth = $participant->getTreeDepth();

        foreach ($juniors as $junior)
        {
            $level = $junior->getTreeDepth() - $participantDepth;

            $profit += $this->getParticipantCommissionValue($participant, $level, $amount);
        }


        return $profit;
    }

    /**
     * Checks level commissions
     * @return boolean TRUE if commissions are ok, otherwise FALSE
     */
    protected function checkLevelsCommissions()
    {
        $reachedLevels = $sum = 0;

        foreach ($this->levelCommissions as $maxLevel => $value)
        {
            $multiplier = $maxLevel - $reachedLevels;

            $sum += ($multiplier * $value);

            $reachedLevels = $maxLevel;
        }

        return ($sum <= 100);
    }

    /**
     * Checks level tresholds
     * @return boolean TRUE if tresholds are ok, otherwise FALSE
     */
    protected function checkLevelsTresholds()
    {

        return !($this->tresholdBasic > $this->levelMax());
    }

    /**
     * Retrieves max levels
     * @return int max treshold
     */
    protected function levelMax()
    {
        $levels = array_keys($this->levelCommissions);
        return array_pop($levels);
    }

    /**
     * Refines commission value
     * @param type $value
     * @param int $precision
     * @return float refined value
     */
    private function refineCommissionValue($value)
    {
        $pointPos = intval(strpos($value, '.'));

        if ($pointPos === 0)
        {
            return $value;
        }

        return floatval(substr($value, 0, $pointPos + $this->commissionPrecision + 1));
    }

    /**
     * Retrieves investors commissions sum
     */
    public static function getInvestorsCommissionsPercentsSum($type, $exclude = [])
    {
        $command = Yii::app()->db->createCommand();
        $command->select('SUM(commission) as sum_commission');
        $command->from(UsrIdentity::model()->tableName());

        $command->where('type=:type', [
            ':type' => $type
        ]);

        if ($exclude)
        {
            $command->andWhere('id NOT IN (:exclude)', [
                ':exclude' => implode(',', $exclude)
            ]);
        }

        return $command->queryScalar();
    }

    /**
     * Retrieves order commissions percetns sum
     */
    public static function getOrderCommissionsPercentsSum(AppTicketOrder $order, $type = null)
    {
        $command = Yii::app()->db->createCommand();
        $command->select('SUM(percent) as sum_percent');
        $command->from(AppTicketOrderCommission::model()->tableName());

        $command->where('app_ticket_order_id=:id', [
            ':id' => $order->primaryKey
        ]);

        if ($type)
        {
            $command->where('type=:type', [
                ':type' => $type
            ]);
        }

        return $command->queryScalar();
    }
}
<?php

/**
 * @author     Jirka Svoboda <code@svobik.com>
 * @copyright  2017 © svobik.com
 * @license    https://www.svobik.com/license.md
 * @timestamp  31/05/2017 11:00
 */

namespace dlds\mlm\helpers;

use Codeception\Util\Debug;
use dlds\mlm\kernel\interfaces\MlmParticipantInterface;
use dlds\mlm\kernel\interfaces\MlmSubjectInterface;
use dlds\mlm\Mlm;
use yii\base\InvalidValueException;

abstract class MlmRewardHelper
{
    /**
     * Retrieves reward level according to given subject and rewarded participant
     * ---
     * @param MlmSubjectInterface $subject
     * @param MlmParticipantInterface $rewarded
     * @return bool|int
     */
    public static function lvl(MlmSubjectInterface $subject, MlmParticipantInterface $rewarded)
    {
        $owner = $subject->__mlmParticipant();

        if (!$owner) {
            return false;
        }

        if (!$rewarded->__mlmIsAncestorOf($owner)) {
            return false;
        }

        return $owner->__mlmLvl() - $rewarded->__mlmLvl();
    }

    /**
     * Retrieves basic reward value according to given subject and rewarded participant
     * ---
     * @param MlmSubjectInterface $subject
     * @param MlmParticipantInterface $rewarded
     * @param int $lvl
     * @return float
     */
    public static function valBasic(MlmSubjectInterface $subject, MlmParticipantInterface $rewarded, $lvl)
    {
        $percentile = $subject->__mlmOwnPercentile($rewarded, $lvl);

        if (false === $percentile) {
            $percentile = static::percentile($rewarded, $lvl, true);
        }

        if ($percentile > 1) {
            throw new InvalidValueException('Percentile cannot be greater then 1');
        }

        return Mlm::round($subject->__mlmAmountBasic() * $percentile);
    }

    /**
     * Retrieves extra reward value according to given subject and rewarded participant
     * ---
     * @param MlmSubjectInterface $subject
     * @param MlmParticipantInterface $rewarded
     * @return float
     */
    public static function valExtra(MlmSubjectInterface $subject, MlmParticipantInterface $rewarded)
    {
        return Mlm::round($subject->__mlmAmountExtra($rewarded));
    }

    /**
     * Retrieves custom reward value according to given subject
     * ---
     * @param MlmSubjectInterface $subject
     * @param MlmParticipantInterface $rewarded
     * @return float
     */
    public static function valCustom(MlmSubjectInterface $subject, MlmParticipantInterface $rewarded)
    {
        return Mlm::round($subject->__mlmAmountCustom($rewarded));
    }

    /**
     * Retrieves percentil of given reward
     * @param MlmParticipantInterface $rewarded
     * @param int $lvl
     * @param boolean $decimally
     * @return float
     */
    public static function percentile(MlmParticipantInterface $rewarded, $lvl, $decimally = false)
    {
        $percentile = MlmRuleHelper::value($lvl, $rewarded->__mlmIsMainParticipant());

        if ($decimally) {
            return $percentile / 100;
        }

        return $percentile;

    }
}
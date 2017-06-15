<?php

/**
 * @author     Jirka Svoboda <code@svobik.com>
 * @copyright  2017 © svobik.com
 * @license    https://www.svobik.com/license.md
 * @timestamp  31/05/2017 11:00
 */

namespace dlds\mlm\helpers;

use dlds\mlm\kernel\interfaces\MlmParticipantInterface;

/**
 * Class MlmParticipantHelper
 * @package dlds\mlm\helpers
 */
abstract class MlmParticipantHelper
{
    /**
     * Compares two participants
     * ---
     * @param MlmParticipantInterface $p1
     * @param MlmParticipantInterface $p2
     * @return bool|int
     */
    public static function compare(MlmParticipantInterface $p1 = null, MlmParticipantInterface $p2 = null)
    {
        if (null === $p1 || null === $p2) {
            return false;
        }

        return $p1->__mlmPrimaryKey() == $p2->__mlmPrimaryKey();
    }

}
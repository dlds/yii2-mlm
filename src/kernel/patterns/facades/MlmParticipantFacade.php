<?php

/**
 * @author     Jirka Svoboda <code@svobik.com>
 * @copyright  2017 © svobik.com
 * @license    https://www.svobik.com/license.md
 * @timestamp  13/06/2017 09:10
 */

namespace dlds\mlm\kernel\patterns\facades;

use dlds\mlm\kernel\interfaces\MlmParticipantInterface;
use dlds\mlm\Mlm;

/**
 * Class MlmParticipantFacade
 * @package dlds\mlm\kernel\patterns\facades
 */
abstract class MlmParticipantFacade
{
    /**
     * Retrieves main participant based on module config "mainParticipantId"
     * @return MlmParticipantInterface
     */
    public static function findOne($pk)
    {
        $class = Mlm::instance()->clsParticipant;

        return call_user_func([$class, '__mlmParticipant'], $pk);
    }

    /**
     * Retrieves main participant based on module config "mainParticipantId"
     * @return MlmParticipantInterface
     */
    public static function findMain()
    {
        $class = Mlm::instance()->clsParticipant;

        return call_user_func([$class, '__mlmMainParticipant']);
    }

}

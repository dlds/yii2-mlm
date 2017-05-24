<?php

/**
 * @link http://www.digitaldeals.cz/
 * @copyright Copyright (c) 2014 Digital Deals s.r.o.
 * @license http://www.digitaldeals.cz/license/
 */

namespace dlds\mlm\components\helpers;

use dlds\mlm\components\interfaces\MlmParticipantInterface;
use dlds\mlm\Mlm;

/**
 * This is the main class of the dlds\mlm component that should be registered as an application component.
 *
 * @author Jiri Svoboda <jiri.svobodao@dlds.cz>
 * @package mlm
 */
class MlmParticipantHelper
{

    /**
     * Retrieves main participant based on module config "mainParticipantId"
     * @return mixed main participant identity or null if does not exists
     */
    public static function findMain()
    {
        $class = Mlm::instance()->clsParticipant;

        return call_user_func_array([$class, 'mlmMainParticipant']);
    }


}

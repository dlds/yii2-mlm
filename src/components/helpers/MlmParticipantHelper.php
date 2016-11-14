<?php

/**
 * @link http://www.digitaldeals.cz/
 * @copyright Copyright (c) 2014 Digital Deals s.r.o.
 * @license http://www.digitaldeals.cz/license/
 */

namespace dlds\mlm\components\helpers;

use dlds\mlm\components\interfaces\MlmParticipantInterface;

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
        if (!$this->mainParticipant) {
            $object = \Yii::createObject($this->participantClass);

            if (!$object instanceof MlmParticipantInterface) {
                throw new \yii\base\Exception('Mlm Participant Class has to implement MlmParticipantInterface');
            }

            $this->mainParticipant = $object::mlmParticipantGeneral();
        }

        return $this->mainParticipant;
    }

    /**
     * Indicates if user is main participant in MLM tree
     * this means user is the first marketer
     * @return boolean TRUE if use is actually main praticipant otherwise FALSE
     */
    public static function isMain(MlmParticipantInterface $participant)
    {
        $main = static::findMain();

        if (!$main) {
            return false;
        }

        return static::main();
    }

}

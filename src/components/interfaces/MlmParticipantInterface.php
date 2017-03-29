<?php

namespace dlds\mlm\components\interfaces;

interface MlmParticipantInterface
{

    /**
     * Indicates if participant rewards are available for extra rewarding or not
     * @return boolen
     */
    public function mlmIsHoarder();

    /**
     * Retrieves multi level marketing level
     * @return int
     */
    public function mlmLvl();

    /**
     * Retrieves multi level marketing ancestor
     * @return MlmParticipantInterface
     */
    public function mlmAncestor();

    /**
     * Retrieves all multi level marketing ancestors to max level
     * @return array
     */
    public function mlmAllAncestors($lvlMax);

    /**
     * Indicates if participant is mlm root
     * @return boolean
     */
    public function mlmIsMainParticipant();

    /**
     * Retrieves multi level marketing main participant
     * @return MlmParticipantInterface
     */
    public static function mlmMainParticipant();


}

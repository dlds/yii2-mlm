<?php

namespace dlds\mlm\kernel\interfaces;

/**
 * Interface MlmParticipantInterface
 * @package dlds\mlm\kernel\interfaces
 */
interface MlmParticipantInterface
{

    /**
     * Retrieves MLM primary key
     * @return int
     */
    public function __mlmPrimaryKey();

    /**
     * Retrieves multi level marketing level
     * @return int
     */
    public function __mlmLvl();

    /**
     * Retrieves MLM ancestor
     * ---
     * When lvl is greater then 1 ancestor on higher level should be retrieved
     * ---
     * @param integer $lvl
     * @return MlmParticipantInterface
     */
    public function __mlmAncestor($lvl = 1);

    /**
     * Retrieves all multi level marketing ancestors to max level
     * @param integer $lvlMax
     * @return array
     */
    public function __mlmAllAncestors($lvlMax);

    /**
     * Indicates if participant rewards are available for extra rewarding or not
     * ---
     * When participant is hoarder then no extra rewarding is allowed.
     * ---
     * @return boolean
     */
    public function __mlmIsHoarder();

    /**
     * Indicates if participant is mlm root
     * @return boolean
     */
    public function __mlmIsMainParticipant();

    /**
     * Indicates if participant is ancestor of another participant
     * @param MlmParticipantInterface $descendant
     * @return boolean
     */
    public function __mlmIsAncestorOf(MlmParticipantInterface $descendant);

    /**
     * Indicates if participant is descendant of another participant
     * @param MlmParticipantInterface $ancestor
     * @return boolean
     */
    public function __mlmIsDescendantOf(MlmParticipantInterface $ancestor);

    /**
     * Retrieves max level of basic reward the participant is eligible to take
     * @return int
     */
    public function __mlmEligibleToLevel();

    /**
     * Indicates if participant is eligible to get basic rewards
     * @param int|null $onLevel
     * @return boolean
     */
    public function __mlmEligibleToBasicRewards($onLevel = null);

    /**
     * Indicates if participant is eligible to get extra rewards
     * @return boolean
     */
    public function __mlmEligibleToExtraRewards();

    /**
     * Indicates if participant is eligible to get custom rewards
     * @return boolean
     */
    public function __mlmEligibleToCustomRewards();

    /**
     * Retrieves multi level marketing participant based on give primary key
     * @param int $pk
     * @return MlmParticipantInterface
     */
    public static function __mlmParticipant($pk);

    /**
     * Retrieves multi level marketing main participant
     * @return MlmParticipantInterface
     */
    public static function __mlmMainParticipant();


}

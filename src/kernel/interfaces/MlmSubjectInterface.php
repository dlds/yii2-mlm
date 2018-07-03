<?php

namespace dlds\mlm\kernel\interfaces;

/**
 * Interface MlmSubjectInterface
 * @package dlds\mlm\kernel\interfaces
 */
interface MlmSubjectInterface
{
    /**
     * Retrieves MLM primary key
     * @return int
     */
    public function __mlmPrimaryKey();

    /**
     * Retrieves subject owner
     * ---
     * @return MlmParticipantInterface
     */
    public function __mlmParticipant();

    /**
     * Retrieves MLM reward type
     * @return string
     */
    public function __mlmType();

    /**
     * Retrieves available amount for basic rewards
     * @return double
     */
    public function __mlmAmountBasic();

    /**
     * Retrieves available amount for basic rewards
     * @param MlmParticipantInterface $profiteer
     * @return double
     */
    public function __mlmAmountExtra(MlmParticipantInterface $profiteer);

    /**
     * Retrieves available amount for basic rewards
     * @param MlmParticipantInterface $profiteer
     * @return double
     */
    public function __mlmAmountCustom(MlmParticipantInterface $profiteer);

    /**
     * Retrieves basic rewards profiteers of given suject
     * ---
     * @return MlmParticipantInterface[]
     */
    public function __mlmBasicProfiteers();

    /**
     * Retrieves extra rewards profiteers of given suject
     * ---
     * @return MlmParticipantInterface[]
     */
    public function __mlmExtraProfiteers();

    /**
     * Retrieves custom rewards profiteers of given suject
     * ---
     * @return MlmParticipantInterface[]
     */
    public function __mlmCustomProfiteers();

    /**
     * Retrieves own basic reward percentile for specific profiteer and level
     * @param MlmParticipantInterface $profiteer
     * @param $lvl
     * @return float|false
     */
    public function __mlmOwnPercentile(MlmParticipantInterface $profiteer, $lvl);

    /**
     * Indicates if Basic Rewards could be generated
     * @return boolean
     */
    public function __mlmCanRewardByBasic();

    /**
     * Indicates if Extra Rewards could be generated
     * @return boolean
     */
    public function __mlmCanRewardByExtra();

    /**
     * Indicates if Custom Rewards could be generated
     * @return boolean
     */
    public function __mlmCanRewardByCustom();

    /**
     * Indicates if rewards can be approved
     * @return boolean
     */
    public function __mlmCanApproveRewards();

    /**
     * Retrieves subject type key
     * @return string
     */
    public static function __mlmTypeKey();
}

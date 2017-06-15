<?php

/**
 * @author     Jirka Svoboda <code@svobik.com>
 * @copyright  2017 © svobik.com
 * @license    http://www.svobik.com/license.md
 * @timestamp  13/06/2017 09:09
 */

namespace dlds\mlm\kernel\patterns\builders\interfaces;

use dlds\mlm\kernel\interfaces\MlmParticipantInterface;
use dlds\mlm\kernel\interfaces\MlmRewardInterface;
use dlds\mlm\kernel\interfaces\MlmSubjectInterface;

/**
 * Interface MlmRewardBuilderInterface
 * ---
 * Basic interface for all reward builders.
 * Defines methods required to build a reward.
 * ---
 * @package dlds\mlm\kernel\patterns\builders\interfaces
 * @see http://code.svobik.com/php-patterns/builder/
 */
interface MlmRewardBuilderInterface
{

    /**
     * Inits builder requirements
     * @param MlmParticipantInterface $participant
     */
    public function init(MlmParticipantInterface $participant);

    /**
     * Sets rewarded participant
     */
    public function setParticipant();

    /**
     * Sets source subject for reward
     */
    public function setSubject();

    /**
     * Sets reward value
     */
    public function setReward();

    /**
     * Sets reward status
     */
    public function setStatus();

    /**
     * Sets reward as locked or unlocked
     */
    public function setIsLocked();

    /**
     * Sets reward as final or available to use as MlmSubjectInterface
     */
    public function setIsFinal();

    /**
     * Retrieves result of building
     * @return MlmRewardInterface|boolean
     */
    public function result();
}
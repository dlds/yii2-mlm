<?php

/**
 * @author     Jirka Svoboda <code@svobik.com>
 * @copyright  2017 © svobik.com
 * @license    https://www.svobik.com/license.md
 * @timestamp  14/06/2017 10:26
 */

namespace dlds\mlm\kernel\interfaces\queries;

use dlds\mlm\kernel\interfaces\MlmParticipantInterface;

/**
 * Interface MlmSubjectQueryInterface
 * @package dlds\mlm\kernel\interfaces\queries
 */
interface MlmSubjectQueryInterface
{
    /**
     * Queries subjects based on given participant interface
     * @param MlmParticipantInterface $participant
     * @return MlmSubjectQueryInterface
     */
    public function __mlmOwner(MlmParticipantInterface $participant);

    /**
     * Queries subjects expecting any kind of rewards
     * @return MlmSubjectQueryInterface
     */
    public function __mlmExpectingRewards();

    /**
     * Queries only subjects where rewards can be approved
     * @return mixed
     */
    public function __mlmCanApproveRewards($state = true);
}

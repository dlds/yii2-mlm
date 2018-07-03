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
 * Interface MlmRewardQueryInterface
 * @package dlds\mlm\kernel\interfaces\queries
 */
interface MlmRewardQueryInterface
{
    /**
     * Operators
     */
    const OP_YOUNGER = '>=';
    const OP_OLDER = '<=';

    /**
     * Queries rewards based on given participant interface
     * @param MlmParticipantInterface $participant
     * @return MlmRewardQueryInterface
     */
    public function __mlmProfiteer(MlmParticipantInterface $participant);

    /**
     * Queries rewards based on given source id and type
     * @param integer $id
     * @param string|null $type
     * @return MlmRewardQueryInterface
     */
    public function __mlmSource($id, $type = null);

    /**
     * Queries rewards based on given pending state
     * ---
     * When state is true only pending rewards should be retrieved
     * ---
     * @param boolean $state
     * @return MlmRewardQueryInterface
     */
    public function __mlmPending($state = true);

    /**
     * Queries rewards based on given approved state
     * ---
     * When state is true only approved rewards should be retrieved
     * ---
     * @param boolean $state
     * @return MlmRewardQueryInterface
     */
    public function __mlmApproved($state = true);

    /**
     * Queries rewards based on given denied state
     * ---
     * When state is true only denied rewards should be retrieved
     * ---
     * @param boolean $state
     * @return MlmRewardQueryInterface
     */
    public function __mlmDenied($state = true);

    /**
     * Queries rewards based on given paid state
     * ---
     * When state is true only paid rewards should be retrieved
     * ---
     * @param boolean $state
     * @return MlmRewardQueryInterface
     */
    public function __mlmPaid($state = true);

    /**
     * Queries rewards based on given locked state
     * ---
     * When state is true only locked rewards should be retrieved
     * ---
     * @param boolean $state
     * @return MlmRewardQueryInterface
     */
    public function __mlmLocked($state = true);

    /**
     * Queries rewards based on given final state
     * ---
     * When state is true only final rewards should be retrieved
     * ---
     * @param boolean $state *
     * @return MlmRewardQueryInterface
     */
    public function __mlmFinal($state = true);

    /**
     * Queries rewards based on given age and operator
     * ---
     * For older then use '<='
     * For younger then use '>='
     * ---
     * @param integer $value
     * @param string $operator
     * @return MlmRewardQueryInterface
     */
    public function __mlmAge($value, $operator = self::OP_OLDER);

    /**
     * Queries only rewards expecting to be approved
     * @param integer|null $delay
     * @return mixed
     */
    public function __mlmExpectingVerification($delay = null);
}

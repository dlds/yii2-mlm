<?php

/**
 * @author     Jirka Svoboda <code@svobik.com>
 * @copyright  2017 © svobik.com
 * @license    https://www.svobik.com/license.md
 * @timestamp  14/06/2017 10:26
 */

namespace dlds\mlm\kernel\interfaces\queries;

/**
 * Interface MlmSubjectQueryInterface
 * @package dlds\mlm\kernel\interfaces\queries
 */
interface MlmParticipantQueryInterface
{
    /**
     * Queries only main participant
     * @return MlmParticipantQueryInterface
     */
    public function __mlmIsMain();

    /**
     * Indicates if participant is eligible to get basic rewards
     * ---
     * When state is true only eligible participants should be retrieved
     * ---
     * @param boolean $state
     * @return mixed
     */
    public function __mlmEligibleToBasicRewards($state = true);

    /**
     * Indicates if participant is eligible to get extra rewards
     * ---
     * When state is true only eligible participants should be retrieved
     * ---
     * @param boolean $state
     * @return mixed
     */
    public function __mlmEligibleToExtraRewards($state = true);

    /**
     * Indicates if participant is eligible to get custom rewards
     * ---
     * When state is true only eligible participants should be retrieved
     * ---
     * @param boolean $state
     * @return mixed
     */
    public function __mlmEligibleToCustomRewards($state = true);
}

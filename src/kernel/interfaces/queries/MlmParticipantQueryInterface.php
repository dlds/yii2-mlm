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
}

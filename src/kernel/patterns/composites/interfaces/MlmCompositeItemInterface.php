<?php

/**
 * @author     Jirka Svoboda <code@svobik.com>
 * @copyright  2017 © svobik.com
 * @license    https://www.svobik.com/license.md
 * @timestamp  14/06/2017 10:53
 */

namespace dlds\mlm\kernel\patterns\composites\interfaces;

/**
 * Interface MlmCompositeItemInterface
 * @package dlds\mlm\kernel\patterns\composites\interfaces
 */
interface MlmCompositeItemInterface
{

    /**
     * Retrieves item value
     */
    public function value();

    /**
     * Retrieves item attributes
     * @param boolean $refresh
     */
    public function attributes($refresh = false);
}
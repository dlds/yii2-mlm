<?php

/**
 * @author     Jirka Svoboda <code@svobik.com>
 * @copyright  2017 © svobik.com
 * @license    https://www.svobik.com/license.md
 * @timestamp  14/06/2017 10:52
 */

namespace dlds\mlm\kernel\patterns\composites\interfaces;

/**
 * Class MlmCompositeInterface
 * @package dlds\mlm\kernel\patterns\composites\interfaces
 */
interface MlmCompositeInterface
{

    /**
     * Adds new item to composite
     * @param MlmCompositeItemInterface $item
     */
    public function add(MlmCompositeItemInterface $item);

    /**
     * Retrieves oldest item from composite
     * @return MlmCompositeItemInterface|null
     */
    public function pop();

    /**
     * Clears all composite
     * @return boolean
     */
    public function clear();

    /**
     * Retrieves composite size
     * ---
     * When precedent is given then difference among size and given value is retrieved
     * ---
     * @param integer $precedent
     * @return int
     */
    public function size($precedent = 0);
}
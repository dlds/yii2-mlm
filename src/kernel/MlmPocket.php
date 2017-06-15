<?php

/**
 * @author     Jirka Svoboda <code@svobik.com>
 * @copyright  2017 © svobik.com
 * @license    https://www.svobik.com/license.md
 * @timestamp  14/06/2017 10:26
 */

namespace dlds\mlm\kernel;

use dlds\mlm\kernel\patterns\composites\interfaces\MlmCompositeInterface;
use dlds\mlm\kernel\patterns\composites\interfaces\MlmCompositeItemInterface;

/**
 * Class MlmPocket
 * @package dlds\mlm\kernel\patterns\singletons
 */
class MlmPocket implements MlmCompositeInterface
{
    /**
     * @var MlmPocket
     */
    private static $_instance = null;

    /**
     * @var int
     */
    protected $counter = 0;

    /**
     * @var bool
     */
    protected $persistent = false;

    /**
     * @var MlmCompositeItemInterface[]
     */
    private $_items = [];

    /**
     * MlmPocket constructor.
     */
    private function __construct()
    {
        // silent
    }

    /**
     * Retrieves singleton instance
     * @return MlmPocket
     */
    public static function instance()
    {
        if (null === static::$_instance) {
            static::$_instance = new MlmPocket();
        }

        return static::$_instance;
    }

    /**
     * Enable pocket persistence
     * ---
     * Pocket clearing will be PREVENTED
     */
    public function enablePersistence()
    {
        $this->persistent = true;
    }

    /**
     * Disables pocket persistence
     * ---
     * Pocket clearing will be ALLOWED
     */
    public function disablePersistence()
    {
        $this->persistent = false;
    }

    /**
     * @inheritdoc
     */
    public function isEmpty()
    {
        return !($this->size() > 0);
    }

    // <editor-fold defaultstate="collapsed" desc="MlmCompositeInterface methods">

    /**
     * @inheritdoc
     */
    public function add(MlmCompositeItemInterface $item)
    {
        array_unshift($this->_items, $item);
    }

    /**
     * @inheritdoc
     */
    public function pop()
    {
        return array_pop($this->_items);
    }

    /**
     * @inheritdoc
     */
    public function clear()
    {
        if ($this->persistent) {
            return false;
        }

        $this->_items = [];
        return true;
    }

    /**
     * @inheritdoc
     */
    public function size($precedent = 0)
    {
        return count($this->_items) - $precedent;
    }
    // </editor-fold>

}
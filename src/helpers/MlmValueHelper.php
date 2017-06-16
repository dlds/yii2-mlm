<?php

/**
 * @author     Jirka Svoboda <code@svobik.com>
 * @copyright  2017 © svobik.com
 * @license    https://www.svobik.com/license.md
 * @timestamp  31/05/2017 14:26
 */

namespace dlds\mlm\helpers;

class MlmValueHelper
{

    /**
     * Round decimal value always up
     * ---
     * @see http://php.net/manual/en/function.round.php
     * ---
     * @param float $value
     * @param integer $precision
     * @return float
     */
    public static function roundUp($value, $precision)
    {
        $fig = (int)str_pad('1', ++$precision, '0');
        return (ceil($value * $fig) / $fig);
    }

    /**
     * Round decimal value always down
     * ---
     * @see http://php.net/manual/en/function.round.php
     * ---
     * @param float $value
     * @param integer $precision
     * @return float
     */
    public static function roundDown($value, $precision)
    {
        $fig = (int)str_pad('1', ++$precision, '0');
        return (floor($value * $fig) / $fig);
    }
}
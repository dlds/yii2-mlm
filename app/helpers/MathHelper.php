<?php

/**
 * @author     Jirka Svoboda <code@svobik.com>
 * @copyright  2017 © svobik.com
 * @license    https://www.svobik.com/license.md
 * @timestamp  14/06/2017 16:25
 */

namespace dlds\mlm\app\helpers;

/**
 * Class MathHelper
 * @package dlds\mlm\app\helpers
 */
abstract class MathHelper
{
    /**
     * Adds VAT part to given amount based on percentile
     * @param float $amount
     * @param int $percentile
     * @return float
     */
    public static function addVat($amount, $percentile)
    {
        return $amount * static::ratio($percentile);
    }

    /**
     * Removes VAT part from given amount based on percentile
     * @param float $amount
     * @param int $percentile
     * @return float
     */
    public static function rmvVat($amount, $percentile)
    {
        return $amount / static::ratio($percentile);
    }

    /**
     * Retrieves ratio from given percentile
     * @param $percentile
     * @return float|int
     */
    private static function ratio($percentile)
    {
        return (100 + $percentile) / 100;
    }
}
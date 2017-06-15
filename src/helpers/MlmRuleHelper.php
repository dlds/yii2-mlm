<?php

/**
 * @author     Jirka Svoboda <code@svobik.com>
 * @copyright  2017 © svobik.com
 * @license    https://www.svobik.com/license.md
 * @timestamp  31/05/2017 14:26
 */

namespace dlds\mlm\helpers;

use dlds\mlm\Mlm;
use yii\helpers\ArrayHelper;

class MlmRuleHelper
{

    /**
     * Retrieves maximum rule level
     * @return int
     */
    public static function maxLvl()
    {
        return max(array_keys(Mlm::rules()));
    }

    /**
     * Retrieves rule value
     * ---
     * If all following is true than sum of all following rules until max rule and current rule is retrieved
     * ---
     * @param int $lvl
     * @param boolean $allFollowing
     * @return float
     */
    public static function value($lvl, $allFollowing = false)
    {
        if ($allFollowing) {

            $val = 0;

            for ($i = $lvl; $i <= static::maxLvl(); $i++) {
                $val += static::value($i, false);
            }

            return $val;
        }

        return ArrayHelper::getValue(Mlm::rules(), $lvl, 0);
    }
}
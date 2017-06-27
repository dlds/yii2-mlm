<?php

/**
 * @author     Jirka Svoboda <code@svobik.com>
 * @copyright  2017 © svobik.com
 * @license    https://www.svobik.com/license.md
 * @timestamp  27/06/2017 23:49
 */

namespace dlds\mlm\app\helpers;

use yii\db\ActiveQuery;

/**
 * Class QueryHelper
 * @package dlds\mlm\app\helpers
 */
abstract class QueryHelper
{
    /**
     * Adds relations table alias
     * ---
     * Also adds alias to relation onCondition - tableName is replaced
     * ---
     * @param ActiveQuery $q
     */
    public static function relAlias(ActiveQuery &$q, $alias)
    {
        $q->alias($alias);

        $onCondition = [];

        while (!empty($q->on)) {

            $onCondition[static::alias(key($q->on), $q->from)] = static::alias(array_shift($q->on), $q->from);

        }

        $q->on = $onCondition;
    }

    /**
     * Replaces tableName inside given column with alias based on gived definitions
     * ---
     * Definitions should be given in format ['alias' => 'tableName']
     * ---
     * @param $column
     * @param $definitions
     * @return mixed
     */
    public static function alias($column, $definitions)
    {
        foreach ($definitions as $alias => $tableName) {
            $column = str_replace($tableName . '.', $alias . '.', $column);
        }

        return $column;
    }
}
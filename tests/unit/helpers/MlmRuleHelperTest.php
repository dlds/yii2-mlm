<?php

/**
 * @author     Jirka Svoboda <code@svobik.com>
 * @copyright  2017 © svobik.com
 * @license    https://www.svobik.com/license.md
 * @timestamp  16/06/2017 07:39
 */

namespace dlds\mlm\tests\unit\helpers;

use dlds\mlm\helpers\MlmRuleHelper;

/**
 * Class MlmRuleHelperTest
 * @package dlds\mlm\tests\unit\helpers
 */
class MlmRuleHelperTest extends \Codeception\Test\Unit
{
    /**
     * @var \dlds\mlm\tests\UnitTester
     */
    protected $tester;

    /**
     * Tests max level
     * ---
     * @see MlmRuleHelper
     */
    public function testMaxLvl()
    {
        verify(MlmRuleHelper::maxLvl())->equals(5);
    }

    /**
     * Tests max level
     * ---
     * @see MlmRuleHelper
     */
    public function testValue()
    {
        verify(MlmRuleHelper::value(1, false))->equals(20);
        verify(MlmRuleHelper::value(3, false))->equals(10);
        verify(MlmRuleHelper::value(5, false))->equals(1);

        verify(MlmRuleHelper::value(1, true))->equals(50);
        verify(MlmRuleHelper::value(3, true))->equals(15);
        verify(MlmRuleHelper::value(5, true))->equals(1);
    }

}
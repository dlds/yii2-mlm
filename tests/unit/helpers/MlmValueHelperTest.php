<?php

/**
 * @author     Jirka Svoboda <code@svobik.com>
 * @copyright  2017 © svobik.com
 * @license    https://www.svobik.com/license.md
 * @timestamp  16/06/2017 07:39
 */

namespace dlds\mlm\tests\unit\helpers;

use dlds\mlm\helpers\MlmValueHelper;

/**
 * Class MlmValueHelperTest
 * @package dlds\mlm\tests\unit\helpers
 */
class MlmValueHelperTest extends \Codeception\Test\Unit
{
    /**
     * @var \dlds\mlm\tests\UnitTester
     */
    protected $tester;

    /**
     * Tests rounding up
     * ---
     * @see MlmValueHelper
     */
    public function testRoundUp()
    {
        verify(MlmValueHelper::roundUp(1.12345, 4))->equals(1.1235);
        verify(MlmValueHelper::roundUp(1.12345, 3))->equals(1.124);
        verify(MlmValueHelper::roundUp(1.12345, 2))->equals(1.13);
        verify(MlmValueHelper::roundUp(1.12345, 1))->equals(1.2);
    }

    /**
     * Tests rounding down
     * ---
     * @see MlmValueHelper
     */
    public function testRoundDown()
    {
        verify(MlmValueHelper::roundDown(1.12345, 4))->equals(1.1234);
        verify(MlmValueHelper::roundDown(1.12345, 3))->equals(1.123);
        verify(MlmValueHelper::roundDown(1.12345, 2))->equals(1.12);
        verify(MlmValueHelper::roundDown(1.12345, 1))->equals(1.1);
    }

}
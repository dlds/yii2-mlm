<?php

namespace dlds\mlm\tests\helpers;


use dlds\mlm\helpers\MlmRuleHelper;

class MlmRuleFacadeTest extends \Codeception\Test\Unit
{
    /**
     * @var \dlds\mlm\tests\UnitTester
     */
    protected $tester;

    /**
     * Tests maximum level
     * @see MlmRuleHelper::maxLvl()
     */
    public function testMaxLvl()
    {
        $lvl = MlmRuleHelper::maxLvl();

        verify($lvl)->equals(5);
    }

    /**
     * Tests single rule value
     * @see MlmRuleHelper::value()
     */
    public function testValues()
    {
        $expected = [
            1 => 20,
            2 => 15,
            3 => 10,
            4 => 4,
            5 => 1,
        ];

        foreach ($expected as $lvl => $val) {
            verify(MlmRuleHelper::value($lvl))->equals($val);
        }
    }

    /**
     * Tests grouping rules values
     * @see MlmRuleHelper::value()
     */
    public function testBunchValues()
    {
        $expected = [
            1 => 50,
            2 => 30,
            3 => 15,
            4 => 5,
            5 => 1,
        ];

        foreach ($expected as $lvl => $val) {
            verify(MlmRuleHelper::value($lvl, true))->equals($val);
        }
    }
}
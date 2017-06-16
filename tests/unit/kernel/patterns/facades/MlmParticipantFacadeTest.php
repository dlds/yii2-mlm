<?php

namespace dlds\mlm\tests\unit\kernel\patterns\facades;

use dlds\mlm\kernel\interfaces\MlmParticipantInterface;
use dlds\mlm\kernel\patterns\facades\MlmParticipantFacade;
use dlds\mlm\tests\_fixtures\ParticipantFixture;

class MlmParticipantFacadeTest extends \Codeception\Test\Unit
{
    /**
     * @var \dlds\mlm\tests\UnitTester
     */
    protected $tester;

    /**
     * @return array
     */
    public function _fixtures()
    {
        return ['participant' => ParticipantFixture::className()];
    }

    /**
     * Tests finding main participant
     * ---
     * Main participant in MLM structure is always tree root
     * ---
     * @see MlmParticipantFacade
     */
    public function testMainParticipant()
    {
        $main = MlmParticipantFacade::findMain();

        verify($main)->notNull();
        verify($main)->isInstanceOf(MlmParticipantInterface::class);
        verify($main->__mlmPrimaryKey())->equals(1);
    }

}
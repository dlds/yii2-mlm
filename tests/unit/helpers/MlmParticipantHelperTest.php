<?php

/**
 * @author     Jirka Svoboda <code@svobik.com>
 * @copyright  2017 © svobik.com
 * @license    https://www.svobik.com/license.md
 * @timestamp  16/06/2017 07:39
 */

namespace dlds\mlm\tests\unit\helpers;

use dlds\mlm\app\models\Participant;
use dlds\mlm\app\models\Subject;
use dlds\mlm\helpers\MlmParticipantHelper;
use dlds\mlm\helpers\MlmRewardHelper;
use dlds\mlm\tests\_fixtures\ParticipantFixture;

/**
 * Class MlmParticipantHelperTest
 * @package dlds\mlm\tests\unit\helpers
 */
class MlmParticipantHelperTest extends \Codeception\Test\Unit
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
        return [
            'participant' => ParticipantFixture::className(),
        ];
    }

    /**
     * Tests participant comparsion
     * ---
     * @see MlmParticipantHelper
     */
    public function testCompare()
    {
        $p11 = Participant::findOne(13131);
        $p12 = Participant::findOne(13131);
        $p21 = Participant::findOne(13);
        $p22 = Participant::findOne(13);

        verify(MlmParticipantHelper::compare($p11, $p12))->true();
        verify(MlmParticipantHelper::compare($p12, $p11))->true();

        verify(MlmParticipantHelper::compare($p21, $p22))->true();
        verify(MlmParticipantHelper::compare($p22, $p21))->true();

        verify(MlmParticipantHelper::compare($p11, $p21))->false();
        verify(MlmParticipantHelper::compare($p21, $p11))->false();

        verify(MlmParticipantHelper::compare($p11, $p22))->false();
        verify(MlmParticipantHelper::compare($p22, $p11))->false();

        verify(MlmParticipantHelper::compare($p12, $p21))->false();
        verify(MlmParticipantHelper::compare($p21, $p12))->false();

        verify(MlmParticipantHelper::compare($p12, $p22))->false();
        verify(MlmParticipantHelper::compare($p22, $p12))->false();
    }



}
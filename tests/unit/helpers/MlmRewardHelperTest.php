<?php

/**
 * @author     Jirka Svoboda <code@svobik.com>
 * @copyright  2017 © svobik.com
 * @license    https://www.svobik.com/license.md
 * @timestamp  16/06/2017 07:39
 */

namespace dlds\mlm\tests\unit\helpers;

use Codeception\Util\Stub;
use dlds\mlm\app\models\Participant;
use dlds\mlm\app\models\Subject;
use dlds\mlm\helpers\MlmRewardHelper;
use dlds\mlm\tests\_fixtures\ParticipantFixture;
use dlds\mlm\tests\_fixtures\SubjectFixture;
use yii\base\InvalidValueException;

/**
 * Class MlmRewardHelperTest
 * @package dlds\mlm\tests\unit\helpers
 */
class MlmRewardHelperTest extends \Codeception\Test\Unit
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
            'subject' => SubjectFixture::className()
        ];
    }

    /**
     * Tests level calculation
     * ---
     * @see MlmRewardHelper
     */
    public function testLvl()
    {
        $s1 = Subject::findOne(1);
        verify($s1)->notNull();

        $p11 = Participant::findOne(13131);
        verify($p11)->notNull();

        verify(MlmRewardHelper::lvl($s1, $p11))->equals(1);

        $p12 = Participant::findOne(13);
        verify($p12)->notNull();

        verify(MlmRewardHelper::lvl($s1, $p12))->equals(4);

        $p13 = Participant::findOne(14);
        verify($p13)->notNull();

        verify(MlmRewardHelper::lvl($s1, $p13))->false();
    }

    /**
     * Tests basic value calculation
     * ---
     * @see MlmRewardHelper
     */
    public function testValBasic()
    {
        $s1 = Subject::findOne(1);
        verify($s1)->notNull();

        $p11 = Participant::findOne(13131);
        verify($p11)->notNull();

        verify(MlmRewardHelper::valBasic($s1, $p11, 1))->equals(200);

        $mockSubject = Stub::make(Subject::className(), [
            '__mlmOwnPercentile' => function () {
                return 100;
            },
        ]);

        $this->expectException(InvalidValueException::class);
        verify(MlmRewardHelper::valBasic($mockSubject, $p11, 1))->false();
    }

    /**
     * Tests extra value calculation
     * ---
     * @see MlmRewardHelper
     */
    public function testValExtra()
    {
        $mockParticipant = Stub::make(Participant::className());

        $mockSubject = Stub::make(Subject::className(), [
            '__mlmAmountExtra' => function () {
                return 100.5;
            },
        ]);

        verify(MlmRewardHelper::valExtra($mockSubject, $mockParticipant, 1))->equals(100.5);
    }

    /**
     * Tests custom value calculation
     * ---
     * @see MlmRewardHelper
     */
    public function testValCustom()
    {
        $mockParticipant = Stub::make(Participant::className());

        $mockSubject = Stub::make(Subject::className(), [
            '__mlmAmountCustom' => function () {
                return 30.5;
            },
        ]);

        verify(MlmRewardHelper::valCustom($mockSubject, $mockParticipant, 1))->equals(30.5);
    }

    /**
     * Tests percentile calculation
     * ---
     * @see MlmRewardHelper
     */
    public function testPercentile()
    {
        $mockParticipant = Stub::make(Participant::className(), [
            '__mlmIsMainParticipant' => function () {
                return false;
            }
        ]);

        verify(MlmRewardHelper::percentile($mockParticipant, 1, false))->equals(20);
        verify(MlmRewardHelper::percentile($mockParticipant, 1, true))->equals(0.2);

        verify(MlmRewardHelper::percentile($mockParticipant, 2, false))->equals(15);
        verify(MlmRewardHelper::percentile($mockParticipant, 2, true))->equals(0.15);
    }

}
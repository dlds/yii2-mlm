<?php

namespace dlds\mlm\tests\unit;

use dlds\mlm\app\models\Participant;
use dlds\mlm\app\models\rewards\RwdBasic;
use dlds\mlm\app\models\rewards\RwdCustom;
use dlds\mlm\app\models\rewards\RwdExtra;
use dlds\mlm\kernel\interfaces\MlmParticipantInterface;
use dlds\mlm\Mlm;
use dlds\mlm\tests\_fixtures\ParticipantFixture;
use dlds\mlm\tests\_fixtures\RwdBasicFixture;
use dlds\mlm\tests\_fixtures\RwdCustomFixture;
use dlds\mlm\tests\_fixtures\SubjectFixture;

class MlmTest extends \Codeception\Test\Unit
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
            'subject' => SubjectFixture::className(),
            'rwd_basic' => RwdBasicFixture::className(),
            'rwd_custom' => RwdCustomFixture::className(),
        ];
    }

    /**
     * Tests configured rules
     * @see Mlm::rules()
     */
    public function testRules()
    {
        $rules = Mlm::rules();

        verify($rules)->equals([
            1 => 20,
            2 => 15,
            3 => 10,
            4 => 4,
            5 => 1,
        ]);
    }

    /**
     * Tests configuration
     */
    public function testConfig()
    {
        $clsBasic = Mlm::clsRewardBasic();
        verify($clsBasic)->equals(RwdBasic::className());

        $clsExtra = Mlm::clsRewardExtra();
        verify($clsExtra)->equals(RwdExtra::className());

        $clsCustom = Mlm::clsRewardCustom();
        verify($clsCustom)->equals(RwdCustom::className());

        $clsParticipant = Mlm::clsParticipant();
        verify($clsParticipant)->equals(Participant::className());
    }

    /**
     * Tests mlm root
     * @see Mlm::root()
     */
    public function testRoot()
    {
        $root = Mlm::root();

        verify($root)->notNull();
        verify($root)->isInstanceOf(MlmParticipantInterface::class);
        verify($root->__mlmPrimaryKey())->equals(1);
    }

    /**
     * Tests mlm participant
     * @see Mlm::participant()
     */
    public function testParticipant()
    {
        $first = Mlm::participant(1);

        verify($first)->notNull();
        verify($first)->isInstanceOf(MlmParticipantInterface::class);
        verify($first->__mlmPrimaryKey())->equals(1);

        $second = Mlm::participant(11);

        verify($second)->notNull();
        verify($second)->isInstanceOf(MlmParticipantInterface::class);
        verify($second->__mlmPrimaryKey())->equals(11);

        $third = Mlm::participant(12);

        verify($third)->notNull();
        verify($third)->isInstanceOf(MlmParticipantInterface::class);
        verify($third->__mlmPrimaryKey())->equals(12);

        $third = Mlm::participant(12);

        verify($third)->notNull();
        verify($third)->isInstanceOf(MlmParticipantInterface::class);
        verify($third->__mlmPrimaryKey())->equals(12);
    }

    /**
     * Tests mlm rounding
     */
    public function testRound()
    {
        $instance = Mlm::instance();

        $instance->roundPrecision = 4;
        $instance->roundMode = Mlm::MLM_ROUND_DOWN;

        $value = Mlm::round(1.12345);
        verify($value)->equals(1.1234);

        $value = Mlm::round(1.12349);
        verify($value)->equals(1.1234);

        $value = Mlm::round(1.12341);
        verify($value)->equals(1.1234);

        $instance->roundMode = Mlm::MLM_ROUND_UP;

        $value = Mlm::round(1.12345);
        verify($value)->equals(1.1235);

        $value = Mlm::round(1.12349);
        verify($value)->equals(1.1235);

        $value = Mlm::round(1.12341);
        verify($value)->equals(1.1235);

        $instance->roundMode = PHP_ROUND_HALF_UP;

        $value = Mlm::round(1.12341);
        verify($value)->equals(1.1234);

        $value = Mlm::round(1.12345);
        verify($value)->equals(1.1235);

        $value = Mlm::round(1.12349);
        verify($value)->equals(1.1235);

    }
}
<?php

namespace dlds\mlm\tests\functional;

use Codeception\Util\Debug;
use dlds\mlm\app\models\Participant;
use dlds\mlm\app\models\rewards\RwdBasic;
use dlds\mlm\app\models\rewards\RwdCustom;
use dlds\mlm\app\models\Subject;
use dlds\mlm\Mlm;
use dlds\mlm\tests\_fixtures\ParticipantFixture;
use dlds\mlm\tests\_fixtures\RwdBasicFixture;
use dlds\mlm\tests\_fixtures\RwdCustomFixture;
use dlds\mlm\tests\_fixtures\RwdExtraFixture;
use dlds\mlm\tests\_fixtures\SubjectFixture;
use yii\helpers\ArrayHelper;

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
            'rwd_extra' => RwdExtraFixture::className(),
            'rwd_custom' => RwdCustomFixture::className(),
        ];
    }

    /**
     * Tests structure validity
     */
    public function testStructure()
    {
        $_1 = Participant::findOne(1);
        verify($_1->__mlmIsMainParticipant())->true();
        // rewards
        verify($_1->__mlmEligibleToBasicRewards())->true();
        verify($_1->__mlmEligibleToCustomRewards())->true();
        verify($_1->__mlmEligibleToExtraRewards())->false();

        /**
         * Branch 1
         */
        $_11 = Participant::findOne(11);
        verify($_11->__mlmIsDescendantOf($_1))->true();
        // rewards
        verify($_11->__mlmEligibleToBasicRewards())->true();
        verify($_11->__mlmEligibleToCustomRewards())->false();
        verify($_11->__mlmEligibleToExtraRewards())->false();

        $_12 = Participant::findOne(12);
        verify($_12->__mlmIsDescendantOf($_1))->true();
        // rewards
        verify($_12->__mlmEligibleToBasicRewards())->true();
        verify($_12->__mlmEligibleToCustomRewards())->true();
        verify($_12->__mlmEligibleToExtraRewards())->false();

        $_13 = Participant::findOne(13);
        verify($_13->__mlmIsDescendantOf($_1))->true();
        // rewards
        verify($_13->__mlmEligibleToBasicRewards())->true();
        verify($_13->__mlmEligibleToCustomRewards())->false();
        verify($_13->__mlmEligibleToExtraRewards())->false();

        $_14 = Participant::findOne(14);
        verify($_14->__mlmIsDescendantOf($_1))->true();
        // rewards
        verify($_14->__mlmEligibleToBasicRewards())->true();
        verify($_14->__mlmEligibleToCustomRewards())->false();
        verify($_14->__mlmEligibleToExtraRewards())->false();

        /**
         * Branch 12
         */
        $_121 = Participant::findOne(121);
        verify($_121->__mlmIsDescendantOf($_12))->true();
        // rewards
        verify($_121->__mlmEligibleToBasicRewards())->true();
        verify($_121->__mlmEligibleToCustomRewards())->true();
        verify($_121->__mlmEligibleToExtraRewards())->false();

        /**
         * Branch 121
         */
        $_1211 = Participant::findOne(1211);
        verify($_1211->__mlmIsDescendantOf($_121))->true();
        // rewards
        verify($_1211->__mlmEligibleToBasicRewards())->true();
        verify($_1211->__mlmEligibleToCustomRewards())->true();
        verify($_1211->__mlmEligibleToExtraRewards())->true();

        /**
         * Branch 13
         */
        $_131 = Participant::findOne(131);
        verify($_131->__mlmIsDescendantOf($_13))->true();
        // rewards
        verify($_131->__mlmEligibleToBasicRewards())->false();
        verify($_131->__mlmEligibleToCustomRewards())->false();
        verify($_131->__mlmEligibleToExtraRewards())->false();

        /**
         * Branch 131
         */
        $_1311 = Participant::findOne(1311);
        verify($_1311->__mlmIsDescendantOf($_131))->true();
        // rewards
        verify($_1311->__mlmEligibleToBasicRewards())->false();
        verify($_1311->__mlmEligibleToCustomRewards())->false();
        verify($_1311->__mlmEligibleToExtraRewards())->false();

        $_1312 = Participant::findOne(1312);
        verify($_1312->__mlmIsDescendantOf($_131))->true();
        // rewards
        verify($_1312->__mlmEligibleToBasicRewards())->false();
        verify($_1312->__mlmEligibleToCustomRewards())->false();
        verify($_1312->__mlmEligibleToExtraRewards())->false();

        $_1313 = Participant::findOne(1313);
        verify($_1313->__mlmIsDescendantOf($_131))->true();
        // rewards
        verify($_1313->__mlmEligibleToBasicRewards())->true();
        verify($_1313->__mlmEligibleToCustomRewards())->true();
        verify($_1313->__mlmEligibleToExtraRewards())->false();

        /**
         * Branch 1313
         */
        $_13131 = Participant::findOne(13131);
        verify($_13131->__mlmIsDescendantOf($_1313))->true();
        // rewards
        verify($_13131->__mlmEligibleToBasicRewards())->true();
        verify($_13131->__mlmEligibleToCustomRewards())->true();
        verify($_13131->__mlmEligibleToExtraRewards())->false();

        /**
         * Branch 13131
         */
        $_131311 = Participant::findOne(131311);
        verify($_131311->__mlmIsDescendantOf($_13131))->true();
        // rewards
        verify($_131311->__mlmEligibleToBasicRewards())->true();
        verify($_131311->__mlmEligibleToCustomRewards())->true();
        verify($_131311->__mlmEligibleToExtraRewards())->true();

        /**
         * Branch 14
         */
        $_141 = Participant::findOne(141);
        verify($_141->__mlmIsDescendantOf($_14))->true();
        // rewards
        verify($_141->__mlmEligibleToBasicRewards())->false();
        verify($_141->__mlmEligibleToCustomRewards())->true();
        verify($_141->__mlmEligibleToExtraRewards())->false();

        /**
         * Branch 141
         */
        $_1411 = Participant::findOne(1411);
        verify($_1411->__mlmIsDescendantOf($_141))->true();
        // rewards
        verify($_1411->__mlmEligibleToBasicRewards())->true();
        verify($_1411->__mlmEligibleToCustomRewards())->true();
        verify($_1411->__mlmEligibleToExtraRewards())->false();


        $_1412 = Participant::findOne(1412);
        verify($_1412->__mlmIsDescendantOf($_141))->true();
        // rewards
        verify($_1412->__mlmEligibleToBasicRewards())->true();
        verify($_1412->__mlmEligibleToCustomRewards())->true();
        verify($_1412->__mlmEligibleToExtraRewards())->false();


        $_1413 = Participant::findOne(1413);
        verify($_1413->__mlmIsDescendantOf($_141))->true();
        // rewards
        verify($_1413->__mlmEligibleToBasicRewards())->true();
        verify($_1413->__mlmEligibleToCustomRewards())->true();
        verify($_1413->__mlmEligibleToExtraRewards())->false();

        /**
         * Branch 1413
         */
        $_14131 = Participant::findOne(14131);
        verify($_14131->__mlmIsDescendantOf($_1413))->true();
        // rewards
        verify($_14131->__mlmEligibleToBasicRewards())->false();
        verify($_14131->__mlmEligibleToCustomRewards())->true();
        verify($_14131->__mlmEligibleToExtraRewards())->false();

        /**
         * Branch 14131
         */
        $_141311 = Participant::findOne(141311);
        verify($_141311->__mlmIsDescendantOf($_14131))->true();
        // rewards
        verify($_141311->__mlmEligibleToBasicRewards())->true();
        verify($_141311->__mlmEligibleToCustomRewards())->true();
        verify($_141311->__mlmEligibleToExtraRewards())->false();

        /**
         * Branch 14131
         */
        $_141312 = Participant::findOne(141312);
        verify($_141312->__mlmIsDescendantOf($_14131))->true();
        // rewards
        verify($_141312->__mlmEligibleToBasicRewards())->true();
        verify($_141312->__mlmEligibleToCustomRewards())->true();
        verify($_141312->__mlmEligibleToExtraRewards())->false();

        /**
         * Branch 141311
         */
        $_1413111 = Participant::findOne(1413111);
        verify($_1413111->__mlmIsDescendantOf($_141311))->true();
        // rewards
        verify($_1413111->__mlmEligibleToBasicRewards())->true();
        verify($_1413111->__mlmEligibleToCustomRewards())->true();
        verify($_1413111->__mlmEligibleToExtraRewards())->false();

        /**
         * Branch 141312
         */
        $_1413121 = Participant::findOne(1413121);
        verify($_1413121->__mlmIsDescendantOf($_141312))->true();
        // rewards
        verify($_1413121->__mlmEligibleToBasicRewards())->true();
        verify($_1413121->__mlmEligibleToCustomRewards())->true();
        verify($_1413121->__mlmEligibleToExtraRewards())->false();
    }

    /**
     * Tests rewards generation inactive
     */
    public function testInactiveRewardsCreation()
    {
        $mlm = Mlm::instance();

        $_1 = Subject::findOne(1);
        verify($_1)->notNull();

        $_2 = Subject::findOne(2);
        verify($_2)->notNull();

        $mlm->isActive = false;

        verify($mlm->createRewards($_1))->false();
        verify($mlm->createRewards($_2))->false();
    }

    /**
     * Tests rewards generation active
     */
    public function testActiveRewardsCreation()
    {
        $mlm = Mlm::instance();

        $_1 = Subject::findOne(1);
        verify($_1)->notNull();

        $_2 = Subject::findOne(2);
        verify($_2)->notNull();

        $_3 = Subject::findOne(3);
        verify($_3)->notNull();

        $_4 = Subject::findOne(4);
        verify($_4)->notNull();

        $mlm->isActive = true;

        $count_1 = 6;
        $count_2 = 4;
        $count_3 = 6;
        $count_4 = 2;

        $pocket = Mlm::pocket();

        $pocket->enablePersistence();

        verify($mlm->createRewards($_1))->equals($count_1);
        verify($mlm->createRewards($_2))->equals($count_2);
        verify($mlm->createRewards($_3))->equals($count_3);
        verify($mlm->createRewards($_4))->equals($count_4);

        // check if second generation is forbidden
        verify($mlm->createRewards($_1))->equals(0);
        verify($mlm->createRewards($_2))->equals(0);
        verify($mlm->createRewards($_3))->equals(0);
        verify($mlm->createRewards($_4))->equals(0);

        $total = $count_1 + $count_2 + $count_3 + $count_4;

        verify($pocket->size())->equals($total);

        while (!$pocket->isEmpty()) {

            $index = ($total - $pocket->size());

            $item = $pocket->pop();

            verify($item)->notNull();

            $expected = static::expectedRewards($index);

            $attrs = $item->attributes(true);

            verify($attrs)->equals($expected, 0.001);
        }

    }

    /**
     */
    public function testRewardsVerification()
    {
        $mlm = Mlm::instance();

        $_1 = RwdBasic::find()->__mlmPending()->one();
        verify($_1)->notNull();
        verify($_1->__mlmStatus())->equals('pending');
        // default delay
        verify($_1->__mlmExpectingApproval(Mlm::delay()))->false();
        verify($_1->__mlmExpectingDeny(Mlm::delay()))->false();
        // no delay
        verify($_1->__mlmExpectingApproval(0))->true();
        verify($_1->__mlmExpectingDeny(0))->false();

        verify($mlm->verifyRewards());

        $_2 = RwdBasic::find()->__mlmPending()->one();
        verify($_2)->notNull();
        verify($_2->__mlmStatus())->equals('pending');
        // default delay
        verify($_2->__mlmExpectingApproval(Mlm::delay()))->false();
        verify($_2->__mlmExpectingDeny(Mlm::delay()))->true();
        // no delay
        verify($_2->__mlmExpectingApproval(0))->false();
        verify($_2->__mlmExpectingDeny(0))->true();
    }

    /**
     * Tests autorun generation / verification
     */
    public function testAutorun()
    {
        $mlm = Mlm::instance();

        verify($mlm->autorun(2))->equals([0, 10]);
        verify($mlm->autorun(2))->equals([0, 8]);

        Debug::debug('===');
        Debug::debug('=== REMOVE DELAY ===');
        Debug::debug('===');

        $mlm->delayPending = 0;

        verify($mlm->autorun(false))->equals([18, 4]);
        verify($mlm->autorun(false))->equals([4, 0]);
    }

    /**
     * Retrieves expected rewards values
     * @param $i
     * @return mixed
     */
    private static function expectedRewards($i)
    {
        $expected = [
            // basic rewards 1
            [
                'usr_rewarded_id' => 13131,
                'subject_id' => 1,
                'subject_type' => 'subject',
                'value' => 200.0,
                'level' => 1,
                'status' => 'pending',
                'is_locked' => 0,
                'is_final' => 1,
                'approved_at' => null,
            ],
            [
                'usr_rewarded_id' => 1313,
                'subject_id' => 1,
                'subject_type' => 'subject',
                'value' => 150.0,
                'level' => 2,
                'status' => 'pending',
                'is_locked' => 0,
                'is_final' => 1,
                'approved_at' => null,
            ],
            [
                'usr_rewarded_id' => 131,
                'subject_id' => 1,
                'subject_type' => 'subject',
                'value' => 100.0,
                'level' => 3,
                'status' => 'pending',
                'is_locked' => 0,
                'is_final' => 1,
                'approved_at' => null,
            ],
            [
                'usr_rewarded_id' => 13,
                'subject_id' => 1,
                'subject_type' => 'subject',
                'value' => 40.0,
                'level' => 4,
                'status' => 'pending',
                'is_locked' => 0,
                'is_final' => 1,
                'approved_at' => null,
            ],
            [
                'usr_rewarded_id' => 1,
                'subject_id' => 1,
                'subject_type' => 'subject',
                'value' => 10.0,
                'level' => 5,
                'status' => 'pending',
                'is_locked' => 0,
                'is_final' => 0,
                'approved_at' => null,
            ],
            // custom rewards 1
            [
                'usr_rewarded_id' => 131311,
                'subject_id' => 1,
                'subject_type' => 'subject',
                'value' => 50,
                'status' => 'pending',
                'is_locked' => 0,
                'approved_at' => null,
            ],
            // basic rewards 2
            [
                'usr_rewarded_id' => 121,
                'subject_id' => 2,
                'subject_type' => 'subject',
                'value' => 90.9090,
                'level' => 1,
                'status' => 'pending',
                'is_locked' => 0,
                'is_final' => 1,
                'approved_at' => null,
            ],
            [
                'usr_rewarded_id' => 12,
                'subject_id' => 2,
                'subject_type' => 'subject',
                'value' => 68.1818,
                'level' => 2,
                'status' => 'pending',
                'is_locked' => 0,
                'is_final' => 1,
                'approved_at' => null,
            ],
            [
                'usr_rewarded_id' => 1,
                'subject_id' => 2,
                'subject_type' => 'subject',
                'value' => 68.1818,
                'level' => 3,
                'status' => 'pending',
                'is_locked' => 0,
                'is_final' => 0,
                'approved_at' => null,
            ],
            // custom rewards 2
            [
                'usr_rewarded_id' => 1211,
                'subject_id' => 2,
                'subject_type' => 'subject',
                'value' => 22.7272,
                'status' => 'pending',
                'is_locked' => 0,
                'approved_at' => null,
            ],
            // basic rewards 3
            [
                'usr_rewarded_id' => 141311,
                'subject_id' => 3,
                'subject_type' => 'subject',
                'value' => 385.9504,
                'level' => 1,
                'status' => 'pending',
                'is_locked' => 0,
                'is_final' => 1,
                'approved_at' => null,
            ],
            [
                'usr_rewarded_id' => 14131,
                'subject_id' => 3,
                'subject_type' => 'subject',
                'value' => 289.4628,
                'level' => 2,
                'status' => 'pending',
                'is_locked' => 0,
                'is_final' => 1,
                'approved_at' => null,
            ],
            [
                'usr_rewarded_id' => 1413,
                'subject_id' => 3,
                'subject_type' => 'subject',
                'value' => 192.9752,
                'level' => 3,
                'status' => 'pending',
                'is_locked' => 0,
                'is_final' => 1,
                'approved_at' => null,
            ],
            [
                'usr_rewarded_id' => 141,
                'subject_id' => 3,
                'subject_type' => 'subject',
                'value' => 77.1900,
                'level' => 4,
                'status' => 'pending',
                'is_locked' => 0,
                'is_final' => 1,
                'approved_at' => null,
            ],
            [
                'usr_rewarded_id' => 14,
                'subject_id' => 3,
                'subject_type' => 'subject',
                'value' => 19.2975,
                'level' => 5,
                'status' => 'pending',
                'is_locked' => 0,
                'is_final' => 1,
                'approved_at' => null,
            ],
            // custom rewards 3
            [
                'usr_rewarded_id' => 1413111,
                'subject_id' => 3,
                'subject_type' => 'subject',
                'value' => 96.4876,
                'status' => 'pending',
                'is_locked' => 0,
                'approved_at' => null,
            ],
            // basic rewards 4
            [
                'usr_rewarded_id' => 141312,
                'subject_id' => 4,
                'subject_type' => 'subject',
                'value' => 187.1900,
                'level' => 1,
                'status' => 'pending',
                'is_locked' => 0,
                'is_final' => 1,
                'approved_at' => null,
            ],
            // custom rewards 4
            [
                'usr_rewarded_id' => 1413121,
                'subject_id' => 4,
                'subject_type' => 'subject',
                'value' => 18.7190,
                'status' => 'pending',
                'is_locked' => 0,
                'approved_at' => null,
            ],

        ];

        return ArrayHelper::getValue($expected, $i);
    }
}
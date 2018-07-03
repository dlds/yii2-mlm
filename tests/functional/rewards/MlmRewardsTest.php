<?php

namespace dlds\mlm\tests\functional;

use Codeception\Util\Debug;
use dlds\mlm\app\models\Participant;
use dlds\mlm\app\models\rewards\RwdBasic;
use dlds\mlm\app\models\Subject;
use dlds\mlm\kernel\interfaces\MlmRewardInterface;
use dlds\mlm\Mlm;
use dlds\mlm\tests\_fixtures\ParticipantFixture;
use dlds\mlm\tests\_fixtures\RwdBasicFixture;
use dlds\mlm\tests\_fixtures\RwdCustomFixture;
use dlds\mlm\tests\_fixtures\RwdExtraFixture;
use dlds\mlm\tests\_fixtures\SubjectFixture;
use yii\helpers\ArrayHelper;

class MlmRewardsTest extends \Codeception\Test\Unit
{
    /**
     * @var \dlds\mlm\tests\UnitTester
     */
    protected $tester;

    /**
     * @inheritdoc
     */
    public function _before()
    {
        $mlm = Mlm::instance();

        $mlm->isLevelRestrictionAllowed = false;
    }

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
     * Tests rewards generation inactive
     */
    public function testInactiveRewardsCreation()
    {
        $mlm = Mlm::instance();

        $mlm->isCreatingActive = false;

        $_1 = Subject::findOne(1);
        verify($_1)->notNull();

        $_2 = Subject::findOne(2);
        verify($_2)->notNull();

        verify($mlm->createRewards($_1))->false();
        verify($mlm->createRewards($_2))->false();
    }

    /**
     * Tests rewards generation active
     */
    public function testActiveRewardsCreation()
    {
        $mlm = Mlm::instance();

        $mlm->isCreatingActive = true;

        $_1 = Subject::findOne(1);
        verify($_1)->notNull();

        $_2 = Subject::findOne(2);
        verify($_2)->notNull();

        $_3 = Subject::findOne(3);
        verify($_3)->notNull();

        $_4 = Subject::findOne(4);
        verify($_4)->notNull();

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

        /** @var MlmRewardInterface $_1 */
        $_1 = RwdBasic::find()->__mlmPending()->one();
        verify($_1)->notNull();
        verify($_1->__mlmStatus())->equals('pending');
        // default delay
        verify($_1->__mlmExpectingApproval(Mlm::delay()))->false();
        verify($_1->__mlmExpectingDeny(Mlm::delay()))->false();
        // no delay
        verify($_1->__mlmExpectingApproval(0))->true();
        verify($_1->__mlmExpectingDeny(0))->false();

        verify($mlm->verifyRewards($_1->__mlmSubject()))->equals(0);

        $mlm->delayPending = 0;

        verify($mlm->verifyRewards($_1->__mlmSubject()))->equals(6);
      }

    /**
     * Tests autorun generation / verification
     */
    public function testAutorun()
    {
        $this->tester->haveFixtures($this->_fixtures());

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
            // custom rewards 4
            [
                'usr_rewarded_id' => 1413121,
                'subject_id' => 4,
                'subject_type' => 'subject',
                'value' => 18.7190,
                'status' => 'pending',
                'status_reason' => null,
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
                'status_reason' => null,
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
                'status_reason' => null,
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
                'status_reason' => null,
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
                'status_reason' => null,
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
                'status_reason' => null,
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
                'status_reason' => null,
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
                'status_reason' => null,
                'is_locked' => 0,
                'is_final' => 1,
                'approved_at' => null,
            ],
            // custom rewards 2
            [
                'usr_rewarded_id' => 1211,
                'subject_id' => 2,
                'subject_type' => 'subject',
                'value' => 22.7272,
                'status' => 'pending',
                'status_reason' => null,
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
                'status_reason' => null,
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
                'status_reason' => null,
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
                'status_reason' => null,
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
                'status_reason' => null,
                'is_locked' => 0,
                'approved_at' => null,
            ],
            // basic rewards 1
            [
                'usr_rewarded_id' => 13131,
                'subject_id' => 1,
                'subject_type' => 'subject',
                'value' => 200.0,
                'level' => 1,
                'status' => 'pending',
                'status_reason' => null,
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
                'status_reason' => null,
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
                'status_reason' => null,
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
                'status_reason' => null,
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
                'status_reason' => null,
                'is_locked' => 0,
                'is_final' => 0,
                'approved_at' => null,
            ],
        ];

        $index = (count($expected) - 1) - $i;

        return ArrayHelper::getValue($expected, $index);
    }
}
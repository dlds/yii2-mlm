<?php

namespace dlds\mlm\tests\unit\kernel\patterns\facades;

use dlds\mlm\app\models\Subject;
use dlds\mlm\kernel\patterns\facades\MlmSubjectFacade;
use dlds\mlm\tests\_fixtures\ParticipantFixture;
use dlds\mlm\tests\_fixtures\SubjectFixture;
use yii\helpers\ArrayHelper;

class MlmSubjectFacadeTest extends \Codeception\Test\Unit
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
        ];
    }

    /**
     * Tests finding profitable participants
     * ---
     * Finds all ancestors of subject's owner until max MLM level is reached.
     * ---
     * @see MlmSubjectFacade
     */
    public function testProfitableParticipants()
    {
        // verify subject ID 1
        $_1 = Subject::findOne(1);
        verify($_1)->notNull();

        $participants = MlmSubjectFacade::profiteersBasic($_1);

        verify($participants)->notNull();
        verify($participants)->count(5);

        // verify subject ID 2
        $_2 = Subject::findOne(2);
        verify($_2)->notNull();

        $participants = MlmSubjectFacade::profiteersBasic($_2);

        verify($participants)->notNull();
        verify($participants)->count(3);
    }

}
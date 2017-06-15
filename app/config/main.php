<?php

/**
 * @author     Jirka Svoboda <code@svobik.com>
 * @copyright  2017 © svobik.com
 * @license    https://www.svobik.com/license.md
 * @timestamp  31/05/2017 12:43
 */

use dlds\mlm\app\models\Participant;
use dlds\mlm\app\models\rewards\RwdBasic;
use dlds\mlm\app\models\rewards\RwdCustom;
use dlds\mlm\app\models\rewards\RwdExtra;
use dlds\mlm\app\models\Subject;
use dlds\mlm\Mlm;

return [
    'id' => 'app-mlm-console',
    'basePath' => dirname(__DIR__),
    'components' => [
        'mlm' => [
            'class' => Mlm::class,
            'clsParticipant' => Participant::className(),
            'clsRewardBasic' => RwdBasic::className(),
            'clsRewardExtra' => RwdExtra::className(),
            'clsRewardCustom' => RwdCustom::className(),
            'clsSubjects' => [
                Subject::__mlmTypeKey() => Subject::className(),
            ],
            // rules sum is 50%
            'rules' => [
                1 => 20,
                2 => 15,
                3 => 10,
                4 => 4,
                5 => 1,
            ],
            // pending delay is 1 min
            'delayPending' => 60,
            // rewarding is active
            'isActive' => true,
        ],
    ],
];
<?php

/**
 * @author     Jirka Svoboda <code@svobik.com>
 * @copyright  2017 © svobik.com
 * @license    https://www.svobik.com/license.md
 * @timestamp  31/05/2017 13:26
 */

namespace dlds\mlm\app\models;

use dlds\mlm\app\models\rewards\RwdBasic;
use dlds\mlm\app\models\rewards\RwdCustom;
use dlds\mlm\app\models\rewards\RwdExtra;
use dlds\mlm\kernel\interfaces\MlmParticipantInterface;
use dlds\mlm\kernel\interfaces\queries\MlmParticipantQueryInterface;
use dlds\mlm\kernel\traits\MlmParticipantTrait;
use dlds\nestedsets\NestedSetsQueryBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Class ParticipantQuery
 * @package dlds\mlm\app\models
 */
class ParticipantQuery extends ActiveQuery implements MlmParticipantQueryInterface
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        return ArrayHelper::merge($behaviors, [
            [
                'class' => NestedSetsQueryBehavior::className(),
            ],
        ]);
    }

    public function __mlmIsMain()
    {
        return $this->andWhere(['id' => 1]);
    }
}

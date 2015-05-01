<?php

namespace dlds\mlm\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "log_mlm".
 *
 * @property integer $id
 * @property integer $result_saving
 * @property integer $result_generator
 * @property resource $commissions
 * @property integer $created_at
 * @property integer $updated_at
 */
class LogMlm extends \yii\db\ActiveRecord implements \dlds\mlm\interfaces\MlmLogInterface {

    /**
     * Behaviors
     */
    const BEHAVIOR_TIMESTAMP = 'b_timestamp';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return \Yii::$app->mlm->logTable;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['result_saving', 'result_generator'], 'required'],
            [['result_saving', 'result_generator'], 'integer'],
            [['commissions'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
                self::BEHAVIOR_TIMESTAMP => [
                    'class' => TimestampBehavior::className()
                ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('dlds/mlm/log', 'ID'),
            'result_saving' => Yii::t('dlds/mlm/log', 'Result Saving'),
            'result_generator' => Yii::t('dlds/mlm/log', 'Result Generator'),
            'commissions' => Yii::t('dlds/mlm/log', 'Commissions'),
            'created_at' => Yii::t('dlds/mlm/log', 'Created At'),
            'updated_at' => Yii::t('dlds/mlm/log', 'Updated At'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function setResultGenerator($result)
    {
        $this->result_generator = $result;
    }

    /**
     * @inheritdoc
     */
    public function setResultSaving($result)
    {
        $this->result_saving = $result;
    }

    /**
     * @inheritdoc
     */
    public function setCommissions(array $commissions)
    {
        $this->commissions = json_encode($commissions);
    }
}
<?php

/**
 * @author     Jirka Svoboda <code@svobik.com>
 * @copyright  2017 © svobik.com
 * @license    https://www.svobik.com/license.md
 * @timestamp  31/05/2017 13:26
 */

namespace dlds\mlm\app\models\rewards;

use dlds\mlm\app\models\Participant;
use dlds\mlm\kernel\interfaces\MlmRewardInterface;
use dlds\mlm\kernel\interfaces\MlmSubjectInterface;
use dlds\mlm\kernel\traits\MlmRewardExtraTrait;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "rwd_extra".
 *
 * @property integer $id
 * @property integer $usr_rewarded_id
 * @property integer $subject_id
 * @property string $subject_type
 * @property double $value
 * @property string $status
 * @property integer $is_locked
 * @property integer $approved_at
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Participant $usrRewarded
 * @property MlmSubjectInterface $subject
 */
class RwdExtra extends \yii\db\ActiveRecord implements MlmRewardInterface
{
    use MlmRewardExtraTrait;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rwd_extra';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['usr_rewarded_id', 'subject_id', 'subject_type', 'value', 'is_locked'], 'required'],
            [['usr_rewarded_id', 'subject_id', 'is_locked', 'approved_at', 'created_at', 'updated_at'], 'integer'],
            [['subject_type', 'status'], 'string'],
            [['value'], 'number'],
            [['usr_rewarded_id', 'subject_id', 'subject_type'], 'unique', 'targetAttribute' => ['usr_rewarded_id', 'subject_id', 'subject_type'], 'message' => 'The combination of Usr Rewarded ID and Rwd Basic ID has already been taken.'],
            [['usr_rewarded_id'], 'exist', 'skipOnError' => true, 'targetClass' => Participant::className(), 'targetAttribute' => ['usr_rewarded_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'usr_rewarded_id' => 'Usr Rewarded ID',
            'subject_id' => 'Subject ID',
            'subject_type' => 'Subject Type',
            'value' => 'Value',
            'status' => 'Status',
            'is_locked' => 'Is Locked',
            'approved_at' => 'Approved At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsrRewarded()
    {
        return $this->hasOne(Participant::className(), ['id' => 'usr_rewarded_id']);
    }

    /**
     * @inheritdoc
     * @return RwdExtraQuery
     */
    public static function find()
    {
        return new RwdExtraQuery(get_called_class());
    }
}

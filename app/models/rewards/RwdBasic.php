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
use dlds\mlm\kernel\traits\MlmRewardBasicTrait;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "rwd_basic".
 *
 * @property integer $id
 * @property integer $usr_rewarded_id
 * @property integer $subject_id
 * @property string $subject_type
 * @property double $value
 * @property integer $level
 * @property string $status
 * @property integer $is_locked
 * @property integer $is_final
 * @property integer $approved_at
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Participant $usrRewarded
 * @property RwdExtra[] $rwdExtras
 * @property MlmSubjectInterface $subject
 */
class RwdBasic extends ActiveRecord implements MlmRewardInterface, MlmSubjectInterface
{
    use MlmRewardBasicTrait;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rwd_basic';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['usr_rewarded_id', 'subject_id', 'subject_type', 'value', 'level'], 'required'],
            [['usr_rewarded_id', 'subject_id', 'level', 'is_locked', 'is_final', 'approved_at', 'created_at', 'updated_at'], 'integer'],
            [['subject_type', 'status'], 'string'],
            [['value'], 'number'],
            [['subject_id', 'subject_type', 'level'], 'unique', 'targetAttribute' => ['subject_id', 'subject_type', 'level'], 'message' => 'The combination of Subject ID, Subject Type and Level has already been taken.'],
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
            'level' => 'Level',
            'status' => 'Status',
            'is_locked' => 'Is Locked',
            'is_final' => 'Is Final',
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
     * @return RwdBasicQuery
     */
    public static function find()
    {
        return new RwdBasicQuery(get_called_class());
    }
}

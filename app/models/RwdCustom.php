<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "rwd_custom".
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
 */
class RwdCustom extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['usr_rewarded_id', 'value', 'is_locked', 'created_at', 'updated_at'], 'required'],
            [['usr_rewarded_id', 'subject_id', 'is_locked', 'approved_at', 'created_at', 'updated_at'], 'integer'],
            [['subject_type', 'status'], 'string'],
            [['value'], 'number'],
            [['subject_id', 'subject_type'], 'unique', 'targetAttribute' => ['subject_id', 'subject_type'], 'message' => 'The combination of Subject ID and Subject Type has already been taken.'],
            [['usr_rewarded_id'], 'exist', 'skipOnError' => true, 'targetClass' => Participant::className(), 'targetAttribute' => ['usr_rewarded_id' => 'id']],
        ];
    }


}

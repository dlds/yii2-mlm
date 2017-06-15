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
use dlds\mlm\kernel\traits\MlmRewardExtraTrait;

/**
 * This is the model class for table "rwd_extra".
 *
 * @property integer $id
 * @property integer $usr_rewarded_id
 * @property integer $rwd_basic_id
 * @property double $value
 * @property string $status
 * @property integer $is_locked
 * @property integer $approved_at
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property RwdBasic $rwdBasic
 * @property Participant $usrRewarded
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
            [['usr_rewarded_id', 'rwd_basic_id', 'value', 'is_locked', 'created_at', 'updated_at'], 'required'],
            [['usr_rewarded_id', 'rwd_basic_id', 'is_locked', 'approved_at', 'created_at', 'updated_at'], 'integer'],
            [['value'], 'number'],
            [['status'], 'string'],
            [['usr_rewarded_id', 'rwd_basic_id'], 'unique', 'targetAttribute' => ['usr_rewarded_id', 'rwd_basic_id'], 'message' => 'The combination of Usr Rewarded ID and Rwd Basic ID has already been taken.'],
            [['rwd_basic_id'], 'exist', 'skipOnError' => true, 'targetClass' => RwdBasic::className(), 'targetAttribute' => ['rwd_basic_id' => 'id']],
            [['usr_rewarded_id'], 'exist', 'skipOnError' => true, 'targetClass' => Participant::className(), 'targetAttribute' => ['usr_rewarded_id' => 'id']],
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
            'rwd_basic_id' => 'Rwd Basic ID',
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
    public function getRwdBasic()
    {
        return $this->hasOne(RwdBasic::className(), ['id' => 'rwd_basic_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsrRewarded()
    {
        return $this->hasOne(Participant::className(), ['id' => 'usr_rewarded_id']);
    }
}

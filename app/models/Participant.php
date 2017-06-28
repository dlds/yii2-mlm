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
use dlds\mlm\kernel\traits\MlmParticipantTrait;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "participant".
 *
 * @property integer $id
 * @property integer $lft
 * @property integer $rgt
 * @property integer $depth
 *
 * @property RwdBasic[] $rwdBasics
 * @property RwdCustom[] $rwdCustoms
 * @property RwdExtra[] $rwdExtras
 * @property RwdBasic[] $rwdBasics0
 * @property Subject[] $subjects
 */
class Participant extends ActiveRecord implements MlmParticipantInterface
{
    use MlmParticipantTrait;

    /**
     * Primary keys of eligible participants
     */
    const PK_EXTRA_ELIGIBLE = [1211, 131311];
    // not eligible
    const PK_BASIC_NOT_ELIGIBLE = [131, 1311, 1312, 141, 14131];
    const PK_CUSTOM_NOT_ELIGIBLE = [11, 13, 131, 1311, 1312, 14];


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'participant';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['lft', 'rgt', 'depth'], 'required'],
            [['lft', 'rgt', 'depth'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'lft' => 'Lft',
            'rgt' => 'Rgt',
            'depth' => 'Depth',
        ];
    }

    /**
     * @inheritdoc
     * @return ParticipantQuery
     */
    public static function find()
    {
        return new ParticipantQuery(get_called_class());
    }

    // <editor-fold defaultstate="collapsed" desc="Relations methods">

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRwdBasics()
    {
        return $this->hasMany(RwdBasic::className(), ['usr_rewarded_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRwdCustoms()
    {
        return $this->hasMany(RwdCustom::className(), ['usr_rewarded_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRwdExtras()
    {
        return $this->hasMany(RwdExtra::className(), ['usr_rewarded_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRwdBasics0()
    {
        return $this->hasMany(RwdBasic::className(), ['id' => 'rwd_basic_id'])->viaTable('rwd_extra', ['usr_rewarded_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubjects()
    {
        return $this->hasMany(Subject::className(), ['participant_id' => 'id']);
    }
    // </editor-fold>
}

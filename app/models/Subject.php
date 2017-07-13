<?php

/**
 * @author     Jirka Svoboda <code@svobik.com>
 * @copyright  2017 © svobik.com
 * @license    https://www.svobik.com/license.md
 * @timestamp  31/05/2017 13:26
 */

namespace dlds\mlm\app\models;

use dlds\mlm\helpers\MlmParticipantHelper;
use dlds\mlm\helpers\MlmRuleHelper;
use dlds\mlm\kernel\interfaces\MlmParticipantInterface;
use dlds\mlm\kernel\interfaces\MlmSubjectInterface;
use dlds\mlm\kernel\traits\MlmSubjectTrait;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "subject".
 *
 * @property integer $id
 * @property integer $participant_id
 * @property double $amount
 * @property double $amount_vat
 *
 * @property Participant $participant
 */
class Subject extends ActiveRecord implements MlmSubjectInterface
{
    use MlmSubjectTrait;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'subject';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['participant_id'], 'integer'],
            [['amount', 'amount_vat'], 'required'],
            [['amount', 'amount_vat'], 'number'],
            [['participant_id'], 'exist', 'skipOnError' => true, 'targetClass' => Participant::className(), 'targetAttribute' => ['participant_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'participant_id' => 'Participant ID',
            'amount' => 'Amount',
            'amount_vat' => 'Amount Vat',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParticipant()
    {
        return $this->hasOne(Participant::className(), ['id' => 'participant_id']);
    }

    /**
     * @inheritdoc
     * @return SubjectQuery
     */
    public static function find()
    {
        return new SubjectQuery(get_called_class());
    }

    // <editor-fold defaultstate="collapsed" desc="MlmSubjectInterface methods">

    /**
     * @inheritdoc
     */
    public function __mlmAmountCustom(MlmParticipantInterface $profiteer)
    {
        if (!MlmParticipantHelper::compare($profiteer, $this->__mlmParticipant())) {
            return 0;
        }

        return 0.05 * $this->rawAmount();
    }

    /**
     * @inheritdoc
     */
    public function __mlmOwnPercentile(MlmParticipantInterface $profiteer, $lvl)
    {
        /** @var MlmParticipantInterface $ownerAncestor */
        $ownerAncestor = $this->participant->__mlmAncestor();

        // check if owner ancestor is one with specific rewarding rules
        if (!$ownerAncestor || $ownerAncestor->__mlmPrimaryKey() != 141312) {
            return false;
        }

        // check if owner ancestor and given participant for 1. level are equals
        if (1 == $lvl && MlmParticipantHelper::compare($profiteer, $ownerAncestor)) {
            return 0.5;
        }

        return 0;
    }

    /**
     * Retrieves basic rewards profiteers of given suject
     * ---
     * @return MlmParticipantInterface[]
     */
    public function __mlmBasicProfiteers()
    {
        $owner = $this->participant;

        return $owner->ancestors(MlmRuleHelper::maxLvl())->all();
    }

    /**
     * Retrieves extra rewards profiteers of given suject
     * ---
     * @return MlmParticipantInterface[]
     */
    public function __mlmExtraProfiteers()
    {
        return [];
    }

    /**
     * Retrieves custom rewards profiteers of given suject
     * ---
     * @return MlmParticipantInterface[]
     */
    public function __mlmCustomProfiteers()
    {
        return $this->participant;
    }

    // </editor-fold>
}

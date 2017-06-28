<?php

/**
 * @author     Jirka Svoboda <code@svobik.com>
 * @copyright  2017 © svobik.com
 * @license    https://www.svobik.com/license.md
 * @timestamp  14/06/2017 13:59
 */

namespace dlds\mlm\kernel\traits;

use dlds\mlm\app\models\rewards\RwdBasic;
use dlds\mlm\app\models\rewards\RwdExtra;
use dlds\mlm\kernel\interfaces\MlmParticipantInterface;
use dlds\mlm\kernel\interfaces\MlmSubjectInterface;
use dlds\mlm\Mlm;
use yii\helpers\StringHelper;

/**
 * Trait MlmRewardBasicTrait
 * @package dlds\mlm\kernel\traits
 */
trait MlmRewardBasicTrait
{
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubject()
    {
        return $this->hasOne(Mlm::clsSubject($this->subject_type), ['id' => 'subject_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRwdExtras()
    {
        return $this->hasMany(RwdExtra::className(), ['subject_id' => 'id'])->andOnCondition([RwdExtra::tableName() . '.subject_type' => $this->__mlmType()]);
    }

    // <editor-fold defaultstate="collapsed" desc="MlmRewardInterface methods">

    /**
     * @inheritdoc
     */
    public function __mlmPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * @inheritdoc
     */
    public function __mlmSave()
    {
        return $this->save();
    }

    /**
     * @inheritdoc
     */
    public function __mlmRewarded(MlmParticipantInterface $participant = null)
    {
        if (null !== $participant) {
            $this->usr_rewarded_id = $participant->__mlmPrimaryKey();
        }

        return $this->usrRewarded;
    }

    /**
     * @inheritdoc
     */
    public function __mlmSubject(MlmSubjectInterface $subject = null)
    {
        if (null !== $subject) {
            $this->subject_id = $subject->__mlmPrimaryKey();
            $this->subject_type = $subject->__mlmType();
        }

        return $this->subject;
    }

    /**
     * @inheritdoc
     */
    public function __mlmValue($val = null)
    {
        if (null !== $val) {
            $this->value = $val;
        }

        return $this->value;
    }

    /**
     * @inheritdoc
     */
    public function __mlmLevel($lvl = null)
    {
        if (null !== $lvl) {
            $this->level = (int)$lvl;
        }

        return $this->level;
    }

    /**
     * @inheritdoc
     */
    public function __mlmStatus($status = null)
    {
        if (null !== $status) {
            $this->status = $status;
        }

        return $this->status;
    }

    /**
     * @inheritdoc
     */
    public function __mlmIsLocked($state = null)
    {
        if (null !== $state) {
            $this->is_locked = (int)$state;
        }

        return $this->is_locked;
    }

    /**
     * @inheritdoc
     */
    public function __mlmIsFinal($state = null)
    {
        if (null !== $state) {
            $this->is_final = (int)$state;
        }

        return $this->is_final;
    }

    /**
     * @inheritdoc
     */
    public function __mlmExpectingApproval($delay = null)
    {
        if ((time() - $this->created_at) < $delay) {
            return false;
        }

        /** @var MlmParticipantInterface $rewarded */
        $rewarded = $this->__mlmRewarded();

        if (!$rewarded->__mlmEligibleToBasicRewards()) {
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function __mlmExpectingDeny($delay = null)
    {
        if ((time() - $this->created_at) < $delay) {
            return false;
        }

        /** @var MlmParticipantInterface $rewarded */
        $rewarded = $this->__mlmRewarded();

        if ($rewarded->__mlmEligibleToBasicRewards()) {
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function __mlmAttributes($refresh = false)
    {
        if ($refresh) {
            $this->refresh();
        }

        return $this->getAttributes([
            'usr_rewarded_id',
            'subject_id',
            'subject_type',
            'value',
            'level',
            'status',
            'is_locked',
            'is_final',
            'approved_at',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function __mlmApprove()
    {
        $this->status = 'approved';
        $this->approved_at = time();

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function __mlmDeny()
    {
        $this->status = 'denied';
        $this->approved_at = null;

        return $this;
    }

    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="MlmSubjectInterface methods">

    /**
     * @inheritdoc
     */
    public function __mlmParticipant()
    {
        return $this->usrRewarded;
    }

    /**
     * @inheritdoc
     */
    public function __mlmType()
    {
        return static::__mlmTypeKey();
    }

    /**
     * @inheritdoc
     */
    public function __mlmAmountBasic()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function __mlmAmountExtra(MlmParticipantInterface $profiteer)
    {
        if ($profiteer->__mlmPrimaryKey() == 1211) {
            return 0.10 * $this->value;
        }

        if ($profiteer->__mlmPrimaryKey() == 131311) {
            return 0.20 * $this->value;
        }

        return 0;
    }

    /**
     * @inheritdoc
     */
    public function __mlmAmountCustom(MlmParticipantInterface $profiteer)
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function __mlmOwnPercentile(MlmParticipantInterface $profiteer, $lvl)
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function __mlmCanRewardByBasic()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function __mlmCanRewardByExtra()
    {
        return !$this->is_final && !$this->getRwdExtras()->count();
    }

    /**
     * @inheritdoc
     */
    public function __mlmCanRewardByCustom()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function __mlmTypeKey()
    {
        return strtolower(StringHelper::basename(RwdBasic::className()));
    }

    // </editor-fold>
}
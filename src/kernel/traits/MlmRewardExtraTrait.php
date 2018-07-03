<?php

/**
 * @author     Jirka Svoboda <code@svobik.com>
 * @copyright  2017 © svobik.com
 * @license    https://www.svobik.com/license.md
 * @timestamp  14/06/2017 13:59
 */

namespace dlds\mlm\kernel\traits;

use dlds\mlm\app\models\rewards\RwdBasic;
use dlds\mlm\kernel\interfaces\MlmParticipantInterface;
use dlds\mlm\kernel\interfaces\MlmSubjectInterface;
use dlds\mlm\Mlm;

/**
 * Trait MlmRewardExtraTrait
 * @package dlds\mlm\kernel\traits
 */
trait MlmRewardExtraTrait
{
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubject()
    {
        return $this->hasOne(Mlm::clsSubject($this->subject_type), ['id' => 'subject_id']);
    }

    // <editor-fold defaultstate="collapsed" desc="MlmRewardInterface methods">

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
    public function __mlmPrimaryKey()
    {
        return $this->primaryKey;
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
        return null;
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
    public function __mlmStatusReason($reason = null)
    {
        if (null !== $reason) {
            $this->status_reason = $reason;
        }

        return $this->status_reason;
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
        return true;
    }

    /**
     * @inheritdoc
     */
    public function __mlmExpectingApproval($delay = null)
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function __mlmExpectingDeny($delay = null)
    {
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
            'status',
            'status_reason',
            'is_locked',
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
}
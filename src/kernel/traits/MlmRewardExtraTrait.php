<?php

/**
 * @author     Jirka Svoboda <code@svobik.com>
 * @copyright  2017 © svobik.com
 * @license    https://www.svobik.com/license.md
 * @timestamp  14/06/2017 13:59
 */

namespace dlds\mlm\kernel\traits;

use dlds\mlm\kernel\interfaces\MlmParticipantInterface;
use dlds\mlm\kernel\interfaces\MlmSubjectInterface;

/**
 * Trait MlmRewardExtraTrait
 * @package dlds\mlm\kernel\traits
 */
trait MlmRewardExtraTrait
{
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
            $this->rwd_basic_id = $subject->__mlmPrimaryKey();
        }

        return $this->rwdBasic;
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
    public function __mlmAttributes($refresh = false)
    {
        if ($refresh) {
            $this->refresh();
        }

        return $this->getAttributes([
            'usr_rewarded_id',
            'rwd_basic_id',
            'value',
            'status',
            'is_locked',
            'approved_at',
        ]);
    }

    // </editor-fold>
}
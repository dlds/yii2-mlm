<?php

/**
 * @author     Jirka Svoboda <code@svobik.com>
 * @copyright  2017 © svobik.com
 * @license    https://www.svobik.com/license.md
 * @timestamp  13/06/2017 09:10
 */

namespace dlds\mlm\kernel\patterns\builders;

use dlds\mlm\helpers\MlmRewardHelper;
use dlds\mlm\kernel\exceptions\MlmRewardBuilderError;
use dlds\mlm\kernel\interfaces\MlmParticipantInterface;
use dlds\mlm\kernel\interfaces\MlmRewardInterface;
use dlds\mlm\kernel\interfaces\MlmSubjectInterface;
use dlds\mlm\kernel\patterns\builders\interfaces\MlmRewardBuilderInterface;
use dlds\mlm\Mlm;

/**
 * Class MlmRewardBasicBuilder
 * ---
 * Basic rewards builder. Inits and create basic reward.
 * ---
 * @package dlds\mlm\kernel\patterns\builders
 */
class MlmRewardBasicBuilder implements MlmRewardBuilderInterface
{
    /**
     * @var MlmParticipantInterface
     */
    private $_prtc;

    /**
     * @var MlmRewardInterface
     */
    private $_rwd;

    /**
     * @var MlmSubjectInterface
     */
    private $_sbj;

    /**
     * Retrieves new instance
     * @param MlmSubjectInterface $subject
     * @return MlmRewardBasicBuilder
     */
    public static function instance(MlmSubjectInterface $subject)
    {
        return new MlmRewardBasicBuilder($subject);
    }

    /**
     * Creates new instance
     * @param MlmSubjectInterface $subject
     * MlmRewardBasicBuilder constructor.
     */
    private function __construct(MlmSubjectInterface $subject)
    {
        $this->_sbj = $subject;
    }

    // <editor-fold defaultstate="collapsed" desc="MlmRewardBuilderInterface methods">

    /**
     * @inheritdoc
     */
    public function init(MlmParticipantInterface $participant)
    {
        $class = Mlm::clsRewardBasic();

        $this->_rwd = new $class;

        $this->_prtc = $participant;
    }

    /**
     * @inheritdoc
     */
    public function setParticipant()
    {
        $this->_rwd->__mlmRewarded($this->_prtc);
    }

    /**
     * @inheritdoc
     */
    public function setSubject()
    {
        $this->_rwd->__mlmSubject($this->_sbj);
    }

    /**
     * @inheritdoc
     */
    public function setReward()
    {
        if (!$this->_prtc) {
            throw MlmRewardBuilderError::factory($this, sprintf('Cannot call % when participant is null.', __METHOD__));
        }

        $lvl = MlmRewardHelper::lvl($this->_sbj, $this->_prtc);
        $this->_rwd->__mlmLevel($lvl);

        $val = MlmRewardHelper::valBasic($this->_sbj, $this->_prtc, $lvl);
        $this->_rwd->__mlmValue($val);
    }

    /**
     * @inheritdoc
     */
    public function setStatus()
    {
        $mlm = Mlm::instance();

        // when level restriction is allowed and rewarded participant is not eligible to take reward
        if ($mlm->isLevelRestrictionAllowed && !$this->_prtc->__mlmEligibleToBasicRewards($this->_rwd->__mlmLevel())) {

            $this->_rwd->__mlmStatusReason(Mlm::RWS_REASON_NOT_ELIGIBLE);
            return $this->_rwd->__mlmStatus(Mlm::alsStatus(Mlm::RW_STATUS_MISSED));
        }

        return $this->_rwd->__mlmStatus(Mlm::alsStatus(Mlm::RW_STATUS_PENDING));
    }

    /**
     * @inheritdoc
     */
    public function setIsLocked()
    {
        $this->_rwd->__mlmIsLocked(false);
    }

    /**
     * @inheritdoc
     */
    public function setIsFinal()
    {
        if (!$this->_prtc) {
            throw MlmRewardBuilderError::factory($this, sprintf('Cannot call % when participant is null.', __METHOD__));
        }

        $this->_rwd->__mlmIsFinal($this->_prtc->__mlmIsHoarder());
    }

    /**
     * @inheritdoc
     */
    public function result()
    {
        return $this->_rwd;
    }
    // </editor-fold>
}
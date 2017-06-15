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
 * Class MlmRewardCustomBuilder
 * ---
 * Custom rewards builder. Inits and create custom reward.
 * ---
 * @package dlds\mlm\kernel\patterns\builders
 */
class MlmRewardCustomBuilder implements MlmRewardBuilderInterface
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
     * @return MlmRewardCustomBuilder
     */
    public static function instance(MlmSubjectInterface $subject)
    {
        return new MlmRewardCustomBuilder($subject);
    }

    /**
     * Creates new instance
     * @param MlmSubjectInterface $subject
     * MlmRewardCustomBuilder constructor.
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
        $class = Mlm::clsRewardCustom();

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

        $val = MlmRewardHelper::valCustom($this->_sbj, $this->_prtc);

        $this->_rwd->__mlmValue($val);
    }

    /**
     * @inheritdoc
     */
    public function setStatus()
    {
        $this->_rwd->__mlmStatus(Mlm::alsStatus(Mlm::RW_STATUS_PENDING));
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
        // silent
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
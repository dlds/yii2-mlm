<?php

namespace dlds\mlm\kernel\interfaces;

interface MlmRewardInterface
{
    /**
     * Saves record to persistent storage
     * @return boolean
     */
    public function __mlmSave();

    /**
     * Retrieves reward primary key
     * @return integer
     */
    public function __mlmPrimaryKey();

    /**
     * Attaches and retrieves rewarded participant record
     * ---
     * New participant should be set only if is not null.
     * ---
     * @param MlmParticipantInterface $participant
     * @return MlmParticipantInterface
     */
    public function __mlmRewarded(MlmParticipantInterface $participant = null);

    /**
     * Attaches and retrieves subject of reward record
     * ---
     * New subject should be set only if is not null.
     * ---
     * @param MlmSubjectInterface $subject
     * @return MlmSubjectInterface
     */
    public function __mlmSubject(MlmSubjectInterface $subject = null);

    /**
     * Sets and retrieves reward entry value
     * ---
     * New value should be set only if is not null.
     * ---
     * @param double $val
     * @return double
     */
    public function __mlmValue($val = null);

    /**
     * Sets and retrieves reward entry level
     * ---
     * New level should be set only if is not null.
     * ---
     * @param int $lvl
     * @return int
     */
    public function __mlmLevel($lvl = null);

    /**
     * Sets and retrieves reward status
     * ---
     * New status should be set only if is not null.
     * ---
     * @param string $status
     * @return string
     */
    public function __mlmStatus($status = null);

    /**
     * Sets and retrieves IS LOCKED state
     * ---
     * New state should be set only if is not null.
     * ---
     * @param boolean $state
     * @return boolean
     */
    public function __mlmIsLocked($state = null);

    /**
     * Sets and retrieves IS FINAL state
     * ---
     * New state should be set only if is not null.
     * ---
     * @param boolean $state
     * @return boolean
     */
    public function __mlmIsFinal($state = null);

    /**
     * Indicates if reward is expecting approval
     * ---
     * Default MLM delay is given and should be counted
     * ---
     * @param integer $delay
     * @return mixed
     */
    public function __mlmExpectingApproval($delay = null);

    /**
     * Indicates if reward is expecting to be denied
     * ---
     * Default MLM delay is given and should be counted
     * ---
     * @param integer $delay
     * @return mixed
     */
    public function __mlmExpectingDeny($delay = null);

    /**
     * Loads and retrieves mlm specific attributes
     * ---
     * When refresh is true the DB callback should be processed.
     * ---
     * @param boolean $refresh
     * @return array
     */
    public function __mlmAttributes($refresh = false);

    /**
     * Approves reward
     * @return MlmRewardInterface
     */
    public function __mlmApprove();

    /**
     * Denies reward
     * @return MlmRewardInterface
     */
    public function __mlmDeny();
}
<?php

namespace dlds\mlm\components\interfaces;

interface MlmRewardInterface
{

    /**
     * Cretes reward
     * @return boolean
     */
    public function mlmCreate();

    /**
     * Sets reward owner
     * @param MlmParticipantInterface $participant
     */
    public function mlmSetOwner(MlmParticipantInterface $participant);

    /**
     * Sets reward subject
     * @param MlmSubjectInterface $subject
     */
    public function mlmSetSubject(MlmSubjectInterface $subject);

    /**
     * Sets reward value
     * @param int $value
     */
    public function mlmSetValue($value);

    /**
     * Sets reward level
     * @param int $lvl
     */
    public function mlmSetLevel($lvl);

    /**
     * Retrieves reward entry final percent
     * ---
     * When reward is 5% than 0.05 should be retrieved if $asDecimal is true
     * ---
     * @param boolean $asDecimal
     * @return float
     */
    public function mlmFinalPercent($asDecimal = true);

    /**
     * Retrieves final amount of reward entry
     * ---
     * @param boolean $incVat
     * @return mixed
     */
    public function mlmFinalAmount($incVat = false);

    /**
     * Retrieves source amount available for reward
     * ---
     * @param boolean $incVat
     * @return mixed
     */
    public function mlmSourceAmount($incVat = false);

    /**
     * Retrieves source vat
     * ---
     * When 21% is used than 0.21 should be retrieved if $asDecimal is true
     * ---
     * @param boolean $asDecimal
     * @return mixed
     */
    public function mlmSourceVat($asDecimal = true);

    /**
     * Retrieves source amount available for reward
     * ---
     * @return mixed
     */
    public function mlmSource();

    /**
     * Approves single reward entry
     * @param bool $save
     * @return mixed
     */
    public function mlmApprove($save = true);

    /**
     * Denies single reward entry
     * @param bool $save
     * @return mixed
     */
    public function mlmDeny($save = true);

    /**
     * Verify single reward entry
     * ---
     * Calls mlmDeny or mlmApprove
     * ---
     * @param bool $save
     * @return mixed
     */
    public function mlmVerify($save = true);

    /**
     * Retreives mlm withdrawal period
     * ---
     * Entry cannot be approved during withdrawal period
     * ---
     * @param bool $save
     * @return mixed
     */
    public static function mlmWithdrawalPeriod();

    /**
     * Approves bunch of rewards entries eligible to be approved
     * ---
     * @param bool $save
     * @return mixed
     */
    public static function mlmApproveAll();

    /**
     * Approves bunch of rewards entries eligible to be approved
     * ---
     * Based on static::mlmApproveAllCondition()
     * ---
     * @param bool $save
     * @return mixed
     */
    public static function mlmDenyAll();

    /**
     * Retrieves query condition for static::mlmApproveAll()
     * ---
     * @param bool $save
     * @return mixed
     */
    public static function mlmApproveAllCondition();

    /**
     * Retrieves query condition for static::mlmDenyAll()
     * ---
     * @param bool $save
     * @return mixed
     */
    public static function mlmDenyAllCondition();

}

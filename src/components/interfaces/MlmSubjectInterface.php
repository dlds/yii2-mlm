<?php

namespace dlds\mlm\components\interfaces;

interface MlmSubjectInterface
{
    /**
     * Retrieves participant owner
     * ---
     * @return MlmParticipantInterface
     */
    public function mlmSubjectOwner();

    /**
     * Retrieves available amount for basic rewards
     * ---
     * @param boolean $incVat
     * @return mixed
     */
    public function mlmSubjectAmount($incVat = false);

    /**
     * Retrieves available amount for custom rewards
     * ---
     * @param boolean $incVat
     * @return mixed
     */
    public function mlmSubjectAmountCustom($incVat = false);

    /**
     * Retrieves vat
     * ---
     * When 21% is used than 0.21 should be retrieved if $asDecimal is true
     * ---
     * @param boolean $asDecimal
     * @return mixed
     */
    public function mlmSubjectVat($asDecimal = true);

    /**
     * Indicates if Basic Rewards could be generated
     * ---
     * @return boolean
     */
    public function mlmCanRewardByBasic();

    /**
     * Indicates if Custom Rewards could be generated
     * ---
     * @return boolean
     */
    public function mlmCanRewardByCustom();
}

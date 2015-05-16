<?php

namespace dlds\mlm\interfaces;

interface MlmCommissionSourceInterface {

    /**
     * Retrieves source amount to be spread
     * @param $incVat indicates if amount with vat will be returned
     * @return float amount to be spread
     */
    public function getAmount($incVat = true);

    /**
     * Retrieves assigned participant means user model
     * @return MlmParticipantInterface $participant
     */
    public function getParticipant();

    /**
     * Indicates if commissions could be created
     * @return boolean TRUE if could, otherwise FALSE
     */
    public function canCreateCommissions();

    /**
     * Retrieves rewarded participants query
     * @return \yii\db\ActiveQuery rewarded participnats
     */
    public function queryRewardedParticipants();
}
<?php

namespace dlds\mlm\interfaces;

interface MlmCommissionSourceInterface {

    /**
     * Retrieves source amount to be spread
     * @param $incVat indicates if amount with vat will be returned
     * @return float amount to be spread
     */
    public function getAmountToSpread($incVat = true);

    /**
     * Retrieves assigned participant means user model
     * @return MlmParticipantInterface $participant
     */
    public function getParticipant();

    /**
     * Retrieves custom commissions rules for source
     */
    public function getCustomCommissionsRules();

    /**
     * Retrieves custom commissions rules for source
     */
    public function getCustomCommissionsRulesSum();

    /**
     * Retrieves custom commission rule amount
     * @param MlmParticipantInterface $participant
     * @return float
     */
    public function getCustomCommissionRuleAmount(MlmParticipantInterface $participant);

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

    /**
     * Retrieves commission history model
     * @return dlds\mlm\interfaces\MlmCommissionHistoryInterface $model
     */
    public function getHistoryModel();
}
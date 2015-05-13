<?php

namespace dlds\mlm\interfaces;

interface MlmParticipantInterface {

    /**
     * Retrieves participant depth in MLM tree
     * @return int participant profitable tree depth
     */
    public function getTreeDepth();

    /**
     * Retrieves participant commissions query
     * @param MlmCommissionInterface $model
     * @param int $status given status commissions will be filtered by
     * @return \yii\db\ActiveQuery current participant commissions query
     */
    public function getQueryCommissions(MlmCommissionInterface $model, $status = false);

    /**
     * Retrieves participant juniors query
     * @param int $depth maximal depth
     * @param boolean $profitable indicates if only profitables juniors will be retrieved
     * @return \yii\db\ActiveQuery current participant juniors query
     */
    public function getQueryJuniors($depth = null, $profitable = false);

    /**
     * Retrieves participant parents query
     * @param int $depth maximal depth
     * @return \yii\db\ActiveQuery current participant seniors query
     */
    public function getQuerySeniors($depth = null);

    /**
     * Retrieves alphas participants query
     * @return \yii\db\ActiveQuery alphas participants query
     */
    public function getQueryAlphas();

    /**
     * Retrieves betas participants query
     * @return \yii\db\ActiveQuery betas participants query
     */
    public function getQueryBetas();

    /**
     * Retrieves participant personal commission
     * @return float personal commission value
     */
    public function getPersonalCommission();

    /**
     * Retrieves participant personal fee
     * @param boolean $incVat if TRUE fee will be retrieved including vat
     * otherwise fee will be retrieved without vat
     * @return float personal fee value
     */
    public function getPersonalFee($incVat = true);

    /**
     * Indicates if participant can take tree commission for specific level
     * @param int $level tree level
     * @return boolean TRUE if can take otherwise FALSE
     */
    public function canTakeTreeCommission($level);

    /**
     * Indicates if participant has currently paid fee
     * @return boolean TRUE if has paid otherwise FALSE
     */
    public function hasFeePaid();

    /**
     * Indicates if participant is the main one
     * @return boolean TRUE if yes, FALSE otherwise
     */
    public function isMainParticipant();

    /**
     * Retrieves main participant in MLM structure
     * @return MlmParticipantInterface main participant
     */
    public static function getMainParticipant();
}
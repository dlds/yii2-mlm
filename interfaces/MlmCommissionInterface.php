<?php

namespace dlds\mlm\interfaces;

interface MlmCommissionInterface {

    /**
     * Retrieves commissions query with specific status or type assigned
     * @param int $status given status - commissions will be filtered by
     * @param int $type given type - commissions will be filtered by
     * @return \yii\db\ActiveQuery commissions query
     */
    public function getQuery($status = false, $type = false);

    /**
     * Sets commission type
     * @param int $type given type
     */
    public function setType($type);

    /**
     * Sets commission tree level
     * @param int $level given level
     */
    public function setLevel($level);

    /**
     * Sets commission status
     * @param int $status given status
     */
    public function setStatus($status);

    /**
     * Sets commission amount as percent
     * @param float $amount given percents
     */
    public function setAmount($amount);

    /**
     * Sets payment to current commission
     * @param MlmPaymentInterface $payment given payment
     */
    public function setPayment(MlmPaymentInterface $payment);

    /**
     * Sets commission participant means which user is assigned to take this commission
     * @param \dlds\mlm\interfaces\MlmParticipantInterface $participant
     */
    public function setParticipant(MlmParticipantInterface $participant);

    /**
     * Sets commission source means which source (model) was used for generate this commission
     * @param \dlds\mlm\interfaces\MlmCommissionSourceInterface $source
     */
    public function setSource(MlmCommissionSourceInterface $source);

    /**
     * Retrieves commission type
     * @return int type
     */
    public function getType();

    /**
     * Retrieves commission level
     * @return int level
     */
    public function getLevel();

    /**
     * Retrieves commission status
     * @return int status
     */
    public function getStatus();

    /**
     * Retrieves commission amount as percent
     * @return float commission percents
     */
    public function getAmount();

    /**
     * Gets assigned payment
     * @return MlmPaymentInterface payment
     */
    public function getPayment();

    /**
     * Gets commission participant means which user is assigned to this commission
     * @return \dlds\mlm\interfaces\MlmParticipantInterface participant
     */
    public function getParticipant();

    /**
     * Retrieves commission source which was used to generate this commission
     * @return \dlds\mlm\interfaces\MlmCommissionSourceInterface commission source
     */
    public function getSource();
}
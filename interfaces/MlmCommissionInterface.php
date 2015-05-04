<?php

namespace dlds\mlm\interfaces;

interface MlmCommissionInterface {

    /**
     * Sets commission type
     * @param int $type given type
     */
    public function setType($type);

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
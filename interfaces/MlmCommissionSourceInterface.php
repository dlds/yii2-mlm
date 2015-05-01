<?php

namespace dlds\mlm\interfaces;

interface MlmCommissionSourceInterface {

    /**
     * Retrieves source amount to be spread
     * @return float amount to be spread
     */
    public function getAmount();

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
}
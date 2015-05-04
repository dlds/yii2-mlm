<?php

namespace dlds\mlm\interfaces;

interface MlmPaymentsHolderInterface {

    /**
     * Retrieves all held payments
     */
    public function getPayments();

    /**
     * Creates payment for given commission
     * @param MlmCommissionInterface $commission given commission
     */
    public function createPayment(MlmCommissionInterface &$commission);
}
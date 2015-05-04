<?php

namespace dlds\mlm\interfaces;

use dlds\mlm\interfaces\MlmCommissionInterface;

interface MlmPaymentInterface {

    /**
     * Sets payment symbol
     * @param int $symbol given symbol
     */
    public function setSymbol($symbol);

    /**
     * Sets payment amount
     * @param float $amount given amount
     */
    public function setAmount($amount);

    /**
     * Links given commissions to current payment
     * @param MlmCommissionInterface $commission
     */
    public function linkCommission(MlmCommissionInterface $commission);

    /**
     * Retrieves payment symbol
     * @return string payment symbol
     */
    public function getSymbol();

    /**
     * Retrieves payment amount
     * @return float payment amount
     */
    public function getAmount();

    /**
     * Retrieves linked commission
     * @return MlmCommissionInterface linked commissions
     */
    public function getLinkedCommissions();
}
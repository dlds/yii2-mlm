<?php

namespace dlds\mlm\holders;

use yii\helpers\ArrayHelper;
use dlds\mlm\interfaces\MlmCommissionInterface;
use dlds\mlm\interfaces\MlmPaymentsHolderInterface;
use dlds\mlm\interfaces\MlmPaymentInterface;
use dlds\mlm\interfaces\MlmCommissionsHolderInterface;

class BasicPaymentsHolder implements MlmPaymentsHolderInterface {

    /**
     * @var MlmPaymentInterface given payment model used for creating payments
     */
    protected $modelPayment;

    /**
     * @var array payments holder
     */
    private $_holder = [];

    /**
     * @var array assigned commissions
     */
    private $_assignedCommissions = [];

    /**
     * Creates new payments holder
     * @param MlmPaymentInterface $modelPayment
     */
    public function __construct(MlmPaymentInterface $modelPayment)
    {
        $this->modelPayment = $modelPayment;
    }

    /**
     * Retrieves held payments
     * @return array
     */
    public function getPayments()
    {
        return $this->_holder;
    }

    /**
     * Creates new payment for given commission
     * @param MlmCommissionInterface $commission
     */
    public function createPayment(MlmCommissionInterface &$commission)
    {
        $key = $commission->getParticipant()->primaryKey;

        if (!ArrayHelper::keyExists($key, $this->_holder))
        {
            $this->_holder[$key] = clone $this->modelPayment;
        }

        $amount = $this->_holder[$key]->getAmount() + \Yii::$app->mlm->getCommissionValue($commission, false);

        $this->_holder[$key]->setAmount($amount);

        $this->_holder[$key]->linkCommission($commission);
    }

    /**
     * Clears current commission holder
     */
    public function clear()
    {
        $this->_holder = [];

        $this->clearSummmary();
    }

    /**
     * Clears current sum of commissions amount
     */
    private function clearSummmary()
    {
        $this->_summary = [];
    }
}
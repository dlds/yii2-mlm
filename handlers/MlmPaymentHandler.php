<?php

namespace dlds\mlm\handlers;

use dlds\mlm\Mlm;
use dlds\mlm\interfaces\MlmParticipantInterface;
use dlds\mlm\interfaces\MlmCommissionInterface;
use dlds\mlm\interfaces\MlmCommissionsHolderInterface;
use dlds\mlm\interfaces\MlmPaymentsHolderInterface;
use dlds\mlm\interfaces\MlmPaymentInterface;
use dlds\mlm\handlers\MlmResultHandler;

class MlmPaymentHandler {

    /**
     * Enroll results
     */
    const RESULT_NONE = 0;
    const RESULT_ERROR = 10;
    const RESULT_PARTIAL_DONE = 50;
    const RESULT_ALL_DONE = 100;

    /**
     * Enrolls given payments to DB
     * @param MlmCommissionsHolderInterface payments holder
     */
    public static function enroll(MlmPaymentsHolderInterface $holder)
    {
        $payments = $holder->getPayments();

        $result = self::RESULT_NONE;

        // go through all held payments
        foreach ($payments as $payment)
        {
            $transaction = $payment->getDb()->beginTransaction();

            $payment->setSymbol('91'.time());
            // try to save given payment
            if ($payment->save())
            {
                $linkedCommissions = $payment->getLinkedCommissions();

                if (!$linkedCommissions)
                {
                    $transaction->rollBack();

                    // set result to PARTIAL DONE if some payment was saved before
                    // if there is no payments saved before let result to be NONE
                    $result = (self::RESULT_NONE !== $result) ? self::RESULT_PARTIAL_DONE : $result;
                }

                foreach ($linkedCommissions as $commission)
                {
                    $commission->setPayment($payment);
                    
                    if ($commission->save())
                    {
                        $transaction->commit();
                        
                        // set result to ALL DONE if no payment was saved before
                        // if there is any payment saved before let result to be unchanged
                        $result = (self::RESULT_NONE === $result) ? self::RESULT_ALL_DONE : $result;
                    }
                    else
                    {
                        $transaction->rollBack();

                        // set result to PARTIAL DONE if some payment was saved before
                        // if there is no payments saved before let result to be NONE
                        $result = (self::RESULT_NONE !== $result) ? self::RESULT_PARTIAL_DONE : $result;
                    }
                }
            }
            else
            {
                // set result to PARTIAL DONE if some payment was saved before
                // if there is no payments saved before let result to be NONE
                $result = (self::RESULT_NONE !== $result) ? self::RESULT_PARTIAL_DONE : $result;
            }
        }

        return $result;
    }
}
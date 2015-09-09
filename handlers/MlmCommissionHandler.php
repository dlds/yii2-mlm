<?php

namespace dlds\mlm\handlers;

use dlds\mlm\Mlm;
use dlds\mlm\interfaces\MlmParticipantInterface;
use dlds\mlm\interfaces\MlmCommissionInterface;
use dlds\mlm\interfaces\MlmCommissionsHolderInterface;
use dlds\mlm\handlers\MlmResultHandler;

class MlmCommissionHandler {

    /**
     * Enroll results
     */
    const RESULT_FAIL = 0;
    const RESULT_NONE = 5;
    const RESULT_PARTIAL_DONE = 50;
    const RESULT_ALL_DONE = 100;

    /**
     * Tries to move all participant commissions with status "statusFrom"
     * into given status "statusTo"
     * @param MlmParticipantInterface $participant
     * @param MlmCommissionInterface $commission
     * @param int $statusFrom status of commissions that will be searched
     * @param int $statusTo status of commissions wich will be used as the new one
     */
    public static function update(MlmParticipantInterface $participant, MlmCommissionInterface $model, $statusFrom, $statusTo)
    {
        $query = $participant->getQueryCommissions($model, $statusFrom);

        return self::updateAll($query, $statusTo, $model->getDirtyAttributes());
    }

    /**
     * Saves all commissions held in given holder
     * @param MlmCommissionsHolderInterface $holder
     * @param int $statusTo status which will be set as the new one
     * @param array attributes additional attributes that will be set
     * @return int result code
     */
    public static function updateAll(\yii\db\ActiveQueryInterface $query, $statusTo, $attributes = [])
    {
        $allCommissions = $query->all();

        $result = self::RESULT_NONE;

        // go through commissions
        foreach ($allCommissions as $commission)
        {
            if ($commission instanceof MlmCommissionInterface)
            {
                // set commissions status to requested
                $commission->setStatus($statusTo);

                if ($attributes)
                {
                    $commission->setAttributes($attributes);
                }

                if ($commission->save())
                {
                    // set result to ALL DONE if no commission was saved before
                    // if there is any commission saved before let result to be unchanged
                    $result = (self::RESULT_NONE === $result) ? self::RESULT_ALL_DONE : $result;
                }
                else
                {
                    // set result to PARTIAL DONE if some commission was saved before
                    // if there is no commissions saved before let result to be NONE
                    $result = (self::RESULT_NONE !== $result) ? self::RESULT_PARTIAL_DONE : $result;
                }
            }
            else
            {
                // set result to PARTIAL DONE if some commission was saved before
                // if there is no commissions saved before let result to be NONE
                $result = (self::RESULT_NONE !== $result) ? self::RESULT_PARTIAL_DONE : $result;
            }
        }

        return $result;
    }

    /**
     * Enrolls given commissions to DB
     * @param MlmCommissionsHolderInterface commissions holder
     */
    public static function enroll(MlmCommissionsHolderInterface $holder)
    {
        // check commission sum overflow
        if ($holder->getSum() > Mlm::TOTAL_AMOUNT)
        {
            // set generating error
            return self::enrollError($holder, Mlm::RESULT_ERROR_OVERFLOW);
        }

        // check undivided commission
        if ($holder->getSum() < Mlm::TOTAL_AMOUNT)
        {
            // set generating success wit result "WARNING UNDIVIDED"
            return self::enrollSuccess($holder, Mlm::RESULT_WARNING_UNDIVIDED);
        }

        // set generating success
        return self::enrollSuccess($holder, Mlm::RESULT_SUCCESS);
    }

    /**
     * Handle generation error, no commission would be saved
     * @param MlmCommissionsHolderInterface $holder
     * @param int $result generator result
     */
    private static function enrollError(MlmCommissionsHolderInterface $holder, $result)
    {
        // get commission overview to be logged
        $overview = $holder->getCommissionsOverview();

        // log generating and saving result
        MlmResultHandler::logResult($result, self::RESULT_NONE, $overview);

        // notify developer about error
        MlmResultHandler::notify($result, $overview);

        return self::RESULT_NONE;
    }

    /**
     * Handle generation success, all commissions would be saved
     * @param MlmCommissionsHolderInterface $holder
     * @param int $generatingResult generator result
     */
    private static function enrollSuccess(MlmCommissionsHolderInterface $holder, $generatingResult)
    {
        // try to save all commissions
        $savingResult = self::enrollAllHeld($holder);

        // get commission overview to be logged
        $overview = $holder->getCommissionsOverview();

        // log generating and saving results
        MlmResultHandler::logResult($generatingResult, $savingResult, $overview);

        // if any of commission was not saved notify developer
        if (self::RESULT_ALL_DONE !== $savingResult)
        {
            MlmResultHandler::notify(Mlm::RESULT_SUCCESS_PARTIAL, $overview);

            return self::RESULT_PARTIAL_DONE;
        }

        return self::RESULT_ALL_DONE;
    }

    /**
     * Saves all commissions held in given holder
     * @param MlmCommissionsHolderInterface $holder
     * @return array first element as boolean - TRUE if all commissions were succesfully saved, FALSE otherwise
     * second element holds saved commissions
     */
    private static function enrollAllHeld(MlmCommissionsHolderInterface $holder)
    {
        $allCommissions = $holder->getCommissions();

        $result = self::RESULT_NONE;

        // go through all held commissions
        foreach ($allCommissions as $commissions)
        {
            // go through all specific commissions
            foreach ($commissions as $commission)
            {
                // try to save given commission
                if ($commission->save())
                {
                    // set result to ALL DONE if no commission was saved before
                    // if there is any commission saved before let result to be unchanged
                    $result = (self::RESULT_NONE === $result) ? self::RESULT_ALL_DONE : $result;
                }
                else
                {
                    // set result to PARTIAL DONE if some commission was saved before
                    // if there is no commissions saved before let result to be NONE
                    $result = (self::RESULT_NONE !== $result) ? self::RESULT_PARTIAL_DONE : $result;
                }
            }
        }

        return $result;
    }
}
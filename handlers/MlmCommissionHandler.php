<?php

namespace dlds\mlm\handlers;

use dlds\mlm\Mlm;
use dlds\mlm\interfaces\MlmCommissionsHolderInterface;
use dlds\mlm\handlers\MlmResultHandler;

class MlmCommissionHandler {

    /**
     * Saving results
     */
    const SAVE_ALL = 10;
    const SAVE_PARTIAL = 20;
    const SAVE_NONE = 30;

    /**
     * Process commissions save
     * @param MlmCommissionsHolderInterface commissions holder
     */
    public static function process(MlmCommissionsHolderInterface $holder)
    {
        // check commission sum overflow
        if ($holder->getSum() > Mlm::TOTAL_AMOUNT)
        {
            // set generating error
            return self::error($holder, Mlm::RESULT_ERROR_OVERFLOW);
        }

        // check undivided commission
        if ($holder->getSum() < Mlm::TOTAL_AMOUNT)
        {
            // set generating success wit result "WARNING UNDIVIDED"
            return self::success($holder, Mlm::RESULT_WARNING_UNDIVIDED);
        }

        // set generating success
        return self::success($holder, Mlm::RESULT_SUCCESS);
    }

    /**
     * Handle generation error, no commission would be saved
     * @param MlmCommissionsHolderInterface $holder
     * @param int $result generator result
     */
    private static function error(MlmCommissionsHolderInterface $holder, $result)
    {
        // get commission overview to be logged
        $overview = $holder->getCommissionsOverview();

        // log generating and saving result
        MlmResultHandler::logResult($result, self::SAVE_NONE, $overview);

        // notify developer about error
        MlmResultHandler::notify($result, $overview);
    }

    /**
     * Handle generation success, all commissions would be saved
     * @param MlmCommissionsHolderInterface $holder
     * @param int $generatingResult generator result
     */
    private static function success(MlmCommissionsHolderInterface $holder, $generatingResult)
    {
        // try to save all commissions
        $savingResult = self::save($holder);

        // get commission overview to be logged
        $overview = $holder->getCommissionsOverview();

        // log generating and saving results
        MlmResultHandler::logResult($generatingResult, $savingResult, $overview);

        // if any of commission was not saved notify developer
        if (self::SAVE_ALL !== $savingResult)
        {
            MlmResultHandler::notify(Mlm::RESULT_SUCCESS_PARTIAL, $overview);
        }
    }

    /**
     * Saves all commissions held in given holder
     * @param MlmCommissionsHolderInterface $holder
     * @return array first element as boolean - TRUE if all commissions were succesfully saved, FALSE otherwise
     * second element holds saved commissions
     */
    private static function save(MlmCommissionsHolderInterface $holder)
    {
        $allCommissions = $holder->getCommissions();

        $result = self::SAVE_NONE;

        // go through all held commissions
        foreach ($allCommissions as $commissions)
        {
            // go through all specific commissions
            foreach ($commissions as $commission)
            {
                // try to save given commission
                if (!$commission->save())
                {
                    // set result to partial success if some commission was saved before
                    if (self::SAVE_NONE !== $result)
                    {
                        $result = self::SAVE_PARTIAL;
                    }
                }
                else
                {
                    // set result to all saved when there is not any unsuccesfull save
                    if (self::SAVE_NONE === $result)
                    {
                        $result = self::SAVE_ALL;
                    }
                }
            }
        }

        return $result;
    }
}
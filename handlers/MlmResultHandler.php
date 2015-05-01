<?php

namespace dlds\mlm\handlers;

use dlds\mlm\models\LogMlm;

class MlmResultHandler {

    /**
     * Logs mlm generator result
     * @param int $resultGenerating generatoring result code
     * @param int $resultSaving saving result code
     * @param array $overview generating overview
     */
    public static function logResult($resultGenerating, $resultSaving, array $overview)
    {
        // create log only if logging is enabled
        if (\Yii::$app->mlm->dbLogging)
        {
            // create new log object
            $log = new LogMlm();

            $log->setResultGenerator($resultGenerating);
            $log->setResultSaving($resultSaving);
            $log->setCommissions($overview);

            // try to save log, if it was unsuccessful notify developer
            if (!$log->save())
            {
                self::notify($resultGenerating, $overview);
            }
        }
    }

    /**
     * Notify developer about errors
     * @param int $resultGenerating generator result code
     * @param array $overview generating overview
     */
    public static function notify($resultGenerating, array $overview)
    {
        // make notification only if it is enabled
        if (\Yii::$app->mlm->errorsNotifications)
        {
            // notify developer by email
            mail(\Yii::$app->mlm->notifyEmail, \Yii::$app->mlm->getResultMessage($resultGenerating), var_export($overview));
        }
    }
}
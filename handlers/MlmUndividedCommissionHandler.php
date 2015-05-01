<?php

namespace dlds\mlm\handlers;

use dlds\mlm\Mlm;
use dlds\mlm\interfaces\MlmCommissionSourceInterface;
use dlds\mlm\interfaces\MlmCommissionInterface;

class MlmUndividedCommissionHandler {

    /**
     * Created direct commissions based on given commission source
     */
    public static function create(MlmCommissionSourceInterface $source, MlmCommissionInterface $model)
    {
        $commissions = [];

        $model->setType(Mlm::COMMISSION_UNDIVIDED);

        $participant = \Yii::$app->mlm->getMainParticipant();

        if ($participant)
        {
            $amount = \Yii::$app->mlm->getUndividedCommissionAvailableToSpread();

            if ($amount)
            {
                $clone = clone $model;

                $clone->setParticipant($participant);
                $clone->setAmount($amount);
                $clone->setSource($source);

                $commissions[$participant->primaryKey] = $clone;
            }
        }

        return $commissions;
    }
}
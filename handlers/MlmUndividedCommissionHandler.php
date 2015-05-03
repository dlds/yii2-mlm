<?php

namespace dlds\mlm\handlers;

use dlds\mlm\Mlm;
use dlds\mlm\interfaces\MlmCommissionSourceInterface;
use dlds\mlm\interfaces\MlmCommissionInterface;
use dlds\mlm\interfaces\MlmParticipantInterface;

class MlmUndividedCommissionHandler {

    /**
     * Created commissions from currently undivided rest of commissions
     * @param MlmCommissionSourceInterface $source given source the commissions
     * are generated from
     * @param MlmCommissionInterface $model given model the handler will use to
     * create commissions
     * @return array created commissions
     */
    public static function create(MlmCommissionSourceInterface $source, MlmCommissionInterface $model)
    {
        $commissions = [];

        $model->setType(Mlm::COMMISSION_TYPE_UNDIVIDED);

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
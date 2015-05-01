<?php

namespace dlds\mlm\handlers;

use dlds\mlm\Mlm;
use dlds\mlm\interfaces\MlmCommissionSourceInterface;
use dlds\mlm\interfaces\MlmCommissionInterface;

class MlmDirectCommissionHandler {

    /**
     * Created direct commissions based on given commission source
     * @param MlmCommissionSourceInterface $source given source that commission will be generated from
     */
    public static function create(MlmCommissionSourceInterface $source, MlmCommissionInterface $model)
    {
        $commissions = [];

        $model->setType(Mlm::COMMISSION_DIRECT);

        $participants = \Yii::$app->mlm->getDirectCommissionParticipants();

        foreach ($participants as $participant)
        {
            $amount = \Yii::$app->mlm->getParticipantDirectCommissionAmount($participant);

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
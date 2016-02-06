<?php

namespace dlds\mlm\handlers;

use dlds\mlm\Mlm;
use dlds\mlm\interfaces\MlmCommissionSourceInterface;
use dlds\mlm\interfaces\MlmCommissionInterface;

class MlmCustomCommissionHandler {

    /**
     * Created custom commissions based on given commission source
     * @param MlmCommissionSourceInterface $source given source that commission will be generated from
     */
    public static function create(MlmCommissionSourceInterface $source, MlmCommissionInterface $model)
    {
        $commissions = [];

        $model->setType(Mlm::COMMISSION_TYPE_CUSTOM);

        $participants = \Yii::$app->mlm->getCustomCommissionParticipants($source);

        foreach ($participants as $participant)
        {
            $amount = \Yii::$app->mlm->getParticipantCustomCommissionAmount($participant, $source);

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
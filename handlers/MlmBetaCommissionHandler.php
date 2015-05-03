<?php

namespace dlds\mlm\handlers;

use dlds\mlm\Mlm;
use dlds\mlm\interfaces\MlmCommissionSourceInterface;
use dlds\mlm\interfaces\MlmCommissionInterface;

class MlmBetaCommissionHandler extends MlmCommissionHandler {

    /**
     * Created direct commissions based on given commission source
     * @param MlmCommissionSourceInterface $source given source that commission will be generated from
     */
    public static function create(MlmCommissionSourceInterface $source, MlmCommissionInterface $model)
    {
        $commissions = [];

        $toSpread = \Yii::$app->mlm->getBetaCommissionAvailableToSpread();

        if ($toSpread)
        {
            $model->setType(Mlm::COMMISSION_TYPE_BETA);

            $participants = $source->getParticipant()->getQueryBetas()->all();

            foreach ($participants as $participant)
            {
                $amount = \Yii::$app->mlm->getParticipantPersonalCommissionAmount($participant, $toSpread->getAmount());

                if ($amount)
                {
                    $clone = clone $model;

                    $clone->setParticipant($participant);
                    $clone->setAmount($amount);
                    $clone->setSource($source);

                    $commissions[$participant->primaryKey] = $clone;
                }
            }
        }

        return $commissions;
    }
}
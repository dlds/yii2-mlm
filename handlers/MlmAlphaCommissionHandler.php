<?php

namespace dlds\mlm\handlers;

use dlds\mlm\Mlm;
use dlds\mlm\interfaces\MlmCommissionSourceInterface;
use dlds\mlm\interfaces\MlmCommissionInterface;

class MlmAlphaCommissionHandler extends MlmCommissionHandler {

    /**
     * Created direct commissions based on given commission source
     * @param MlmCommissionSourceInterface $source given source that commission will be generated from
     */
    public static function create(MlmCommissionSourceInterface $source, MlmCommissionInterface $model)
    {
        $commissions = [];

        $toSpread = \Yii::$app->mlm->getAlphaCommissionAvailableToSpread();

        if ($toSpread)
        {
            $model->setType(Mlm::COMMISSION_ALPHA);

            $participants = $source->getParticipant()->getQueryAlphas()->all();

            foreach ($participants as $participant)
            {
                $amount = \Yii::$app->mlm->getParticipantPersonalCommissionAmount($participant, $toSpread);

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
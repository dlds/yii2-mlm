<?php

namespace dlds\mlm\handlers;

use dlds\mlm\Mlm;
use dlds\mlm\interfaces\MlmCommissionSourceInterface;
use dlds\mlm\interfaces\MlmCommissionInterface;

class MlmTreeCommissionHandler {

    /**
     * Created direct commissions based on given commission source
     * @param MlmCommissionSourceInterface $source given source that commission will be generated from
     */
    public static function create(MlmCommissionSourceInterface $source, MlmCommissionInterface $model)
    {
        $commissions = [];

        $model->setType(Mlm::COMMISSION_TREE);

        $participants = $source->getParticipant()->getQuerySeniors(\Yii::$app->mlm->getTreeCommissionRuleMaxLevel())->all();

        foreach ($participants as $participant)
        {
            $level = $source->getParticipant()->getTreeDepth() - $participant->getTreeDepth();

            $amount = \Yii::$app->mlm->getParticipantTreeCommissionAmount($participant, $level);

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
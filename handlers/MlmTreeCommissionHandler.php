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

        $model->setType(Mlm::COMMISSION_TYPE_TREE);

        $participants = $source->getParticipant()->getQuerySeniors(\Yii::$app->mlm->getTreeCommissionRuleMaxLevel())->all();

        foreach ($participants as $participant)
        {
            $level = $source->getParticipant()->getTreeDepth() - $participant->getTreeDepth();

            $amount = \Yii::$app->mlm->getParticipantTreeCommissionAmount($participant, $level, $source);

            if ($amount)
            {
                $clone = clone $model;

                $clone->setParticipant($participant);
                $clone->setAmount($amount);
                $clone->setSource($source);
                $clone->setLevel($level);

                $commissions[$participant->primaryKey] = $clone;
            }
            elseif (!\Yii::$app->mlm->keepNotAssignedCommission)
            {
                $mainParticipant = \Yii::$app->mlm->getMainParticipant();

                $amount = \Yii::$app->mlm->getParticipantTreeCommissionAmount($mainParticipant, $level, $source);

                if ($amount)
                {
                    if (!isset($commissions[$mainParticipant->primaryKey]))
                    {
                        $clone = clone $model;

                        $clone->setParticipant($mainParticipant);
                        $clone->setAmount($amount);
                        $clone->setSource($source);
                        $clone->setLevel($level);
                        $clone->setType(Mlm::COMMISSION_TYPE_TREE_NOT_ASSIGNED);

                        $commissions[$mainParticipant->primaryKey] = $clone;
                    }
                    else
                    {
                        $currentAmount = $commissions[$mainParticipant->primaryKey]->getAmount();
                        $commissions[$mainParticipant->primaryKey]->setAmount($currentAmount + $amount);
                    }
                }
            }
        }

        return $commissions;
    }
}
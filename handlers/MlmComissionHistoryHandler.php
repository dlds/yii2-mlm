<?php

namespace dlds\mlm\handlers;

use yii\helpers\ArrayHelper;
use dlds\mlm\Mlm;
use dlds\mlm\interfaces\MlmCommissionSourceInterface;
use dlds\mlm\interfaces\MlmCommissionHistoryInterface;

class MlmComissionHistoryHandler {

    /**
     * Created direct commissions based on given commission source
     * @param MlmCommissionSourceInterface $source given source that commission will be generated from
     */
    public static function create(MlmCommissionSourceInterface $source, MlmCommissionHistoryInterface $model)
    {
        $commissions = [];

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
                $clone->setLevel($level);
                $clone->setType(Mlm::COMMISSION_TYPE_TREE);

                $commissions[$level] = $clone;
            }
        }

        for ($level = 1; $level <= \Yii::$app->mlm->getTreeCommissionRuleMaxLevel(); $level++)
        {
            if (!ArrayHelper::keyExists($level, $commissions))
            {
                $clone = clone $model;

                $clone->setAmount(\Yii::$app->mlm->getTreeCommissionRuleAmount($level));
                $clone->setSource($source);
                $clone->setLevel($level);
                $clone->setType(Mlm::COMMISSION_TYPE_ALPHA);

                $commissions[$level] = $clone;
            }
        }

        return $commissions;
    }
}
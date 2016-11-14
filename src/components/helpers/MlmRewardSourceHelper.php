<?php

namespace dlds\mlm\components\helpers;

use dlds\mlm\Mlm;

class MlmRewardSourceHelper
{

    public function createRewards(MlmRewardSourceInterface $source)
    {
        return Mlm::instance()->createRewards($source);
    }

    public function deleteRewards(MlmRewardSourceInterface $source, $all = false)
    {
        $query = $source->mlmRewards();

        if (!$all) {
            $query->hasStatus(Mlm::RW_STATUS_PENDING);
        }

        return Mlm::instance()->deleteRewards($query);
    }

    public function approveRewards(MlmRewardSourceInterface $source, $all = false)
    {
        $query = $source->mlmRewards();

        if (!$all) {
            $query->hasStatus(Mlm::RW_STATUS_PENDING);
        }

        return Mlm::instance()->approveRewards($query);
    }

    public function denyRewards(MlmRewardSourceInterface $source, $all = false)
    {
        $query = $source->mlmRewards();

        if (!$all) {
            $query->hasStatus(Mlm::RW_STATUS_PENDING);
        }

        return Mlm::instance()->denyRewards($query);
    }

    public function lockRewards(MlmRewardSourceInterface $source)
    {
        $query = $source->mlmRewards()->hasStatus(Mlm::RW_STATUS_APPROVED);
        return Mlm::instance()->lockRewards($query);
    }

    public function unlockRewards(MlmRewardSourceInterface $source)
    {
        $query = $source->mlmRewards()->hasStatus(Mlm::RW_STATUS_APPROVED);
        return Mlm::instance()->unlockRewards($query);
    }

}

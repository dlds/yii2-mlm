<?php

namespace dlds\mlm\components\helpers;

use dlds\mlm\Mlm;

class MlmRewardSourceHelper
{

    /**
     * Creates rewards
     * @param MlmRewardSourceInterface $source
     * @return mixed
     */
    public function createRewards(MlmRewardSourceInterface $source)
    {
        return Mlm::instance()->createRewards($source);
    }

    /**
     * Deletes rewards
     * @param MlmRewardSourceInterface $source
     * @param bool $all
     * @return mixed
     */
    public function deleteRewards(MlmRewardSourceInterface $source, $all = false)
    {
        $query = $source->mlmRewards();

        if (!$all) {
            $query->hasStatus(Mlm::RW_STATUS_PENDING);
        }

        return Mlm::instance()->deleteRewards($query);
    }

    /**
     * Approves rewards
     * @param MlmRewardSourceInterface $source
     * @param bool $all
     * @return mixed
     */
    public function approveRewards(MlmRewardSourceInterface $source, $all = false)
    {
        $query = $source->mlmRewards();

        if (!$all) {
            $query->hasStatus(Mlm::RW_STATUS_PENDING);
        }

        return Mlm::instance()->approveRewards($query);
    }

    /**
     * Denies rewards
     * @param MlmRewardSourceInterface $source
     * @param bool $all
     * @return mixed
     */
    public function denyRewards(MlmRewardSourceInterface $source, $all = false)
    {
        $query = $source->mlmRewards();

        if (!$all) {
            $query->hasStatus(Mlm::RW_STATUS_PENDING);
        }

        return Mlm::instance()->denyRewards($query);
    }

    /**
     * Locks rewards
     * @param MlmRewardSourceInterface $source
     * @return mixed
     */
    public function lockRewards(MlmRewardSourceInterface $source)
    {
        $query = $source->mlmRewards()->hasStatus(Mlm::RW_STATUS_APPROVED);
        return Mlm::instance()->lockRewards($query);
    }

    /**
     * Unlocks rewards
     * @param MlmRewardSourceInterface $source
     * @return mixed
     */
    public function unlockRewards(MlmRewardSourceInterface $source)
    {
        $query = $source->mlmRewards()->hasStatus(Mlm::RW_STATUS_APPROVED);
        return Mlm::instance()->unlockRewards($query);
    }

}

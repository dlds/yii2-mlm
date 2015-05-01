<?php

namespace dlds\mlm\interfaces;

interface MlmLogInterface {

    /**
     * Sets generator result
     * @param int $result given result
     */
    public function setResultGenerator($result);

    /**
     * Sets saving result
     * @param int $result given result
     */
    public function setResultSaving($result);

    /**
     * Sets generated commissions
     * @param array $overview given commissions
     */
    public function setCommissions(array $commissions);
}
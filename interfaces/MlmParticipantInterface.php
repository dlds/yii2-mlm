<?php

namespace dlds\mlm\interfaces;

interface MlmParticipantInterface {

    /**
     * Retrieves participant depth in MLM tree
     */
    public function getTreeDepth();

    /**
     * Retrieves participant jniors query
     * @param int $depth maximal depth
     * @param boolean $profitable indicates if only profitables juniors will be retrieved
     */
    public function getQueryJuniors($depth = null, $profitable = false);

    /**
     * Retrieves participant parents query
     * @param int $depth maximal depth
     */
    public function getQuerySeniors($depth = null);

    /**
     * Retrieves alphas participants query
     */
    public function getQueryAlphas();

    /**
     * Retrieves betas participants query
     */
    public function getQueryBetas();

    /**
     * Retrieves participant personal commission
     */
    public function getPersonalCommission();

    /**
     * Retrieves participant personal fee
     */
    public function getPersonalFee();

    /**
     * Indicates if participant can take tree commission for specific level
     * @param int $level tree level
     */
    public function canTakeTreeCommission($level);
}
<?php

namespace dlds\mlm\interfaces;

interface MlmCommissionsHolderInterface {

    /**
     * Updates single commission in current holder if exists
     * based on commissons key and id
     * commissions are held as [key => [id => commission]]
     * @param mixed $key commissions type
     * @return MlmCommissionInterface currently held commissions
     */
    public function updateCommission($key, $id, MlmCommissionInterface $commission);

    /**
     * Adds commissions to current holder and tag them with given key
     * @param array $comissions
     * @param mixed $key
     */
    public function addCommissions(array $comissions, $key);

    /**
     * Retrieves commissions overview from current holder
     * @return array commissions overview
     */
    public function getCommissionsOverview();

    /**
     * Retrieves commissions from current holder based on given key,
     * if key is false all commissions will be retrieved
     * @param mixed $key commissions type
     * @return array currently held commissions
     */
    public function getCommissions($key = false);

    /**
     * Retrieves single commission from holder
     * based on commissons key and id
     * commissions are held as [key => [id => commission]]
     * @param mixed $key commissions type
     * @return MlmCommissionInterface currently held commissions
     */
    public function getCommission($key, $id);

    /**
     * Retrieves current sum of all held commissions
     * @param mixed $key commissions type meand retrieves sum only of particular
     * commissions tagged by this key
     * @return float current sum value
     */
    public function getSum($key = false);

    /**
     * Cleares current holder
     */
    public function clear();
}
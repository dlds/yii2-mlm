<?php

namespace dlds\mlm\holders;

use yii\helpers\ArrayHelper;
use dlds\mlm\interfaces\MlmCommissionInterface;
use dlds\mlm\interfaces\MlmCommissionsHolderInterface;

class BasicCommissionsHolder implements MlmCommissionsHolderInterface {

    /**
     * Overview
     */
    const OVERVIEW_AMOUNT = 'amount';
    const OVERVIEW_PARTICIPANT = 'participant';
    const OVERVIEW_SOURCE = 'source';
    const OVERVIEW_TYPE = 'type';
    const OVERVIEW_CLASS = 'class';
    const OVERVIEW_ERRORS = 'errors';

    /**
     * @var array commission holder
     */
    private $_holder = [];

    /**
     * @var float holds current sum of held commissions
     */
    private $_summary = [];

    /**
     * Updates single commission in current holder if exists
     * based on commissons key and id
     * commissions are held as [key => [id => commission]]
     * @param mixed $key commissions type
     * @return MlmCommissionInterface currently held commissions
     */
    public function updateCommission($key, $id, MlmCommissionInterface $commission)
    {
        if (ArrayHelper::keyExists(sprintf('%s.%s', $key, $id), $this->_holder))
        {
            $this->_holder[$key][$id] = $commission;
        }
    }

    /**
     * Adds commissions to current holder and tag them with given key
     * @param array $comissions
     * @param mixed $key
     */
    public function addCommissions(array $comissions, $key)
    {
        if (ArrayHelper::keyExists($key, $this->_holder))
        {
            $this->_holder[$key] = ArrayHelper::merge($this->_holder[$key], $comissions);
        }

        $this->_holder[$key] = $comissions;
    }

    /**
     * Retrieves commissions from current holder based on given key,
     * if key is false all commissions will be retrieved
     * @param mixed $key commissions identification key
     * @return array currently held commissions
     */
    public function getCommissionsOverview()
    {
        $overview = [];

        foreach ($this->_holder as $key => $commissions)
        {
            foreach ($commissions as $id => $commission)
            {
                if ($commission instanceof MlmCommissionInterface)
                {
                    $overview[$key][$id] = [
                        self::OVERVIEW_CLASS => get_class($commission),
                        self::OVERVIEW_PARTICIPANT => $commission->getParticipant() ? $commission->getParticipant()->primaryKey : null,
                        self::OVERVIEW_SOURCE => $commission->getSource()->primaryKey,
                        self::OVERVIEW_TYPE => $commission->getType(),
                        self::OVERVIEW_AMOUNT => $commission->getAmount(),
                        self::OVERVIEW_ERRORS => $commission->getErrors(),
                    ];
                }
            }
        }

        return $overview;
    }

    /**
     * Retrieves commissions from current holder based on given key,
     * if key is false all commissions will be retrieved
     * @param mixed $key commissions identification key
     * @return array currently held commissions
     */
    public function getCommissions($key = false)
    {
        if (false === $key)
        {
            return $this->_holder;
        }

        return ArrayHelper::getValue($this->_holder, $key, []);
    }

    /**
     * Retrieves single commissions from holder
     * based on commissons key and id
     * commissions are held as [key => [id => commission]]
     * @param mixed $key commissions type
     * @return MlmCommissionInterface currently held commissions
     */
    public function getCommission($key, $id)
    {
        return ArrayHelper::getValue($this->_holder, sprintf('%s.%s', $key, $id));
    }

    /**
     * Retrieves current sum of all held commissions
     * @param mixed $key commissions identification key
     * @return float current sum value
     */
    public function getSum($key = false)
    {
        if (false === $key)
        {
            $checksum = md5(json_encode($this->_holder));
        }
        else
        {
            $checksum = md5($key.json_encode($this->getCommissions($key)));
        }

        $sum = ArrayHelper::getValue($this->_summary, $checksum, 0);

        if (0 === $sum && !empty($this->_holder))
        {
            foreach ($this->_holder as $i => $commissions)
            {
                if (false === $key || $i == $key)
                {
                    foreach ($commissions as $commission)
                    {
                        if ($commission instanceof MlmCommissionInterface)
                        {
                            $sum += $commission->getAmount();
                        }
                    }
                }
            }
        }

        $this->_summary[$checksum] = $sum;

        return $sum;
    }

    /**
     * Clears current commission holder
     */
    public function clear()
    {
        $this->_holder = [];

        $this->clearSummmary();
    }

    /**
     * Clears current sum of commissions amount
     */
    private function clearSummmary()
    {
        $this->_summary = [];
    }
}
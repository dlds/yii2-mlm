<?php

/**
 * @author     Jirka Svoboda <code@svobik.com>
 * @copyright  2017 © svobik.com
 * @license    https://www.svobik.com/license.md
 * @timestamp  14/06/2017 15:48
 */

namespace dlds\mlm\kernel\traits;

use dlds\mlm\app\helpers\MathHelper;
use dlds\mlm\app\models\Subject;
use dlds\mlm\helpers\MlmParticipantHelper;
use dlds\mlm\kernel\interfaces\MlmParticipantInterface;
use yii\helpers\StringHelper;

/**
 * Trait MlmSubjectTrait
 * @package dlds\mlm\kernel\traits
 */
trait MlmSubjectTrait
{
    /**
     * Retrieves subject raw amount
     * ---
     * Removes VAT part from amount if is needed
     * ---
     * @return float
     */
    public function rawAmount()
    {
        if ($this->amount_vat) {

            return MathHelper::rmvVat($this->amount, $this->amount_vat);
        }

        return $this->amount;
    }

    // <editor-fold defaultstate="collapsed" desc="MlmSubjectInterface methods">

    /**
     * @inheritdoc
     */
    public function __mlmPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * @inheritdoc
     */
    public function __mlmParticipant()
    {
        return $this->participant;
    }

    /**
     * @inheritdoc
     */
    public function __mlmType()
    {
        return static::__mlmTypeKey();
    }

    /**
     * @inheritdoc
     */
    public function __mlmAmountBasic()
    {
        return $this->rawAmount();
    }

    /**
     * @inheritdoc
     */
    public function __mlmAmountExtra(MlmParticipantInterface $profiteer)
    {
        return 0;
    }

    /**
     * @inheritdoc
     */
    public function __mlmAmountCustom(MlmParticipantInterface $profiteer)
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function __mlmOwnPercentile(MlmParticipantInterface $profiteer, $lvl)
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function __mlmCanRewardByBasic()
    {
        return $this->__mlmAmountBasic();
    }

    /**
     * @inheritdoc
     */
    public function __mlmCanRewardByExtra()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function __mlmCanRewardByCustom()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function __mlmTypeKey()
    {
        return strtolower(StringHelper::basename(Subject::className()));
    }

    // </editor-fold>
}
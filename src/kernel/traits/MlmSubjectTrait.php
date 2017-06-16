<?php

/**
 * @author     Jirka Svoboda <code@svobik.com>
 * @copyright  2017 © svobik.com
 * @license    https://www.svobik.com/license.md
 * @timestamp  14/06/2017 15:48
 */

namespace dlds\mlm\kernel\traits;

use Codeception\Util\Debug;
use dlds\mlm\app\helpers\MathHelper;
use dlds\mlm\app\models\rewards\RwdBasic;
use dlds\mlm\app\models\rewards\RwdCustom;
use dlds\mlm\app\models\rewards\RwdExtra;
use dlds\mlm\app\models\Subject;
use dlds\mlm\helpers\MlmParticipantHelper;
use dlds\mlm\kernel\interfaces\MlmParticipantInterface;
use dlds\mlm\kernel\interfaces\queries\MlmRewardQueryInterface;
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

    /**
     * Retrieves basic rewards query
     * @return MlmRewardQueryInterface
     */
    public function getRwdBasic()
    {
        return $this->hasMany(RwdBasic::className(), ['subject_id' => 'id'])->where(['subject_type' => $this->__mlmType()]);
    }

    /**
     * Retrieves extra rewards query
     * @return MlmRewardQueryInterface
     */
    public function getRwdExtra()
    {
        return $this->hasMany(RwdExtra::className(), [1 => 0]);
    }

    /**
     * Retrieves custom rewards query
     * @return MlmRewardQueryInterface
     */
    public function getRwdCustom()
    {
        return $this->hasOne(RwdCustom::className(), ['subject_id' => 'id'])->where(['subject_type' => $this->__mlmType()]);
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
        return !$this->getRwdBasic()->count() && $this->__mlmAmountBasic();
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
        return !$this->getRwdCustom()->count();
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
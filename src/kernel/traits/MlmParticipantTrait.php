<?php

/**
 * @author     Jirka Svoboda <code@svobik.com>
 * @copyright  2017 © svobik.com
 * @license    https://www.svobik.com/license.md
 * @timestamp  01/06/2017 17:18
 */

namespace dlds\mlm\kernel\traits;

use dlds\mlm\app\models\Participant;
use dlds\mlm\kernel\interfaces\MlmParticipantInterface;
use dlds\nestedsets\NestedSetsBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

trait MlmParticipantTrait
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        return ArrayHelper::merge($behaviors, [
            [
                'class' => NestedSetsBehavior::className(),
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function transactions()
    {
        return [
            ActiveRecord::SCENARIO_DEFAULT => ActiveRecord::OP_ALL,
        ];
    }

    // <editor-fold defaultstate="collapsed" desc="MlmParticipantInterface methods">

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
    public function __mlmLvl()
    {
        return $this->depth;
    }

    /**
     * @inheritdoc
     */
    public function __mlmAncestor($lvl = 1)
    {
        return $this->ancestors($lvl)->orderBy(['depth' => SORT_ASC])->one();
    }

    /**
     * @inheritdoc
     */
    public function __mlmAllAncestors($lvlMax)
    {
        return $this->ancestors($lvlMax)->orderBy(['depth' => SORT_DESC])->all();
    }

    /**
     * @inheritdoc
     */
    public function __mlmIsHoarder()
    {
        return !$this->isTreeRoot();
    }

    /**
     * @inheritdoc
     */
    public function __mlmIsMainParticipant()
    {
        return $this->isTreeRoot();
    }

    /**
     * @inheritdoc
     */
    public function __mlmIsAncestorOf(MlmParticipantInterface $descendant)
    {
        return $this->isAncestorOf($descendant);
    }

    /**
     * @inheritdoc
     */
    public function __mlmIsDescendantOf(MlmParticipantInterface $ancestor)
    {
        return $this->isDescendantOf($ancestor);
    }

    /**
     * @inheritdoc
     */
    public static function __mlmParticipant($pk)
    {
        return static::findOne($pk);
    }

    /**
     * @inheritdoc
     */
    public function __mlmEligibleToLevel()
    {
        return $this->eligible_to_level;
    }

    /**
     * @inheritdoc
     */
    public function __mlmEligibleToBasicRewards($onLevel = null)
    {
        $isEligible = !ArrayHelper::isIn($this->__mlmPrimaryKey(), Participant::PK_BASIC_NOT_ELIGIBLE);

        if ($onLevel > 0) {
            $isEligible = $isEligible && ($this->__mlmEligibleToLevel() >= $onLevel);
        }

        return $isEligible;
    }

    /**
     * @inheritdoc
     */
    public function __mlmEligibleToExtraRewards()
    {
        return ArrayHelper::isIn($this->__mlmPrimaryKey(), Participant::PK_EXTRA_ELIGIBLE);
    }

    /**
     * @inheritdoc
     */
    public function __mlmEligibleToCustomRewards()
    {
        return !ArrayHelper::isIn($this->__mlmPrimaryKey(), Participant::PK_CUSTOM_NOT_ELIGIBLE);
    }

    /**
     * @inheritdoc
     */
    public static function __mlmMainParticipant()
    {
        return static::find()->isTreeRoot()->one();
    }

    // </editor-fold>
}
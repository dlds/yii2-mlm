<?php

/**
 * @author     Jirka Svoboda <code@svobik.com>
 * @copyright  2017 © svobik.com
 * @license    https://www.svobik.com/license.md
 * @timestamp  01/06/2017 17:18
 */

namespace dlds\mlm\kernel\traits;

use Codeception\Util\Debug;
use dlds\mlm\kernel\interfaces\MlmParticipantInterface;
use dlds\nestedsets\NestedSetsBehavior;
use dlds\nestedsets\NestedSetsQueryBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

trait MlmParticipantTrait
{
    /**
     * @inheritdoc
     */
    public static function find()
    {
        $query = parent::find();

        $query->attachBehaviors([
            NestedSetsQueryBehavior::className(),
        ]);

        return $query;
    }

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
    public static function __mlmMainParticipant()
    {
        return static::find()->isTreeRoot()->one();
    }

    // </editor-fold>
}
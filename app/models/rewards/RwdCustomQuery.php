<?php

/**
 * @author     Jirka Svoboda <code@svobik.com>
 * @copyright  2017 © svobik.com
 * @license    https://www.svobik.com/license.md
 * @timestamp  31/05/2017 13:26
 */

namespace dlds\mlm\app\models\rewards;

use dlds\mlm\app\models\rewards\RwdBasic;
use dlds\mlm\app\models\rewards\RwdCustom;
use dlds\mlm\app\models\rewards\RwdExtra;
use dlds\mlm\kernel\interfaces\MlmParticipantInterface;
use dlds\mlm\kernel\interfaces\queries\MlmRewardQueryInterface;
use dlds\mlm\kernel\traits\MlmParticipantTrait;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Class ParticipantQuery
 * @package dlds\mlm\app\models
 */
class RwdCustomQuery extends ActiveQuery implements MlmRewardQueryInterface
{
    public function __mlmProfiteer(MlmParticipantInterface $participant)
    {
        $this->andWhere(['usr_rewarded_id' => $participant->__mlmPrimaryKey()]);

        return $this;
    }

    public function __mlmSource($id, $type = null)
    {
        $this->andWhere(['subject_id' => $id]);

        if ($type) {
            $this->andWhere(['subject_type' => $type]);
        }

        return $this;
    }

    public function __mlmApproved($state = true)
    {
        $operand = $state ? '=' : '<>';

        $this->andWhere([$operand, 'status', 'approved']);
    }

    public function __mlmLocked($state = true)
    {
        $operand = $state ? '=' : '<>';

        $this->andWhere([$operand, 'status', 1]);
    }

    public function __mlmFinal($state = true)
    {
        $operand = $state ? '=' : '<>';

        $this->andWhere([$operand, 'status', 1]);
    }

    public function __mlmAge($value = 3600, $operator = self::OP_OLDER)
    {
        $birth = time() - $value;

        $this->andWhere([$operator, 'created_at', $birth]);

        return $this;
    }

}

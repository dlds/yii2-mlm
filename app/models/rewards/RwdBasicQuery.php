<?php

/**
 * @author     Jirka Svoboda <code@svobik.com>
 * @copyright  2017 © svobik.com
 * @license    https://www.svobik.com/license.md
 * @timestamp  31/05/2017 13:26
 */

namespace dlds\mlm\app\models\rewards;

use dlds\mlm\app\helpers\QueryHelper;
use dlds\mlm\kernel\interfaces\MlmParticipantInterface;
use dlds\mlm\kernel\interfaces\queries\MlmParticipantQueryInterface;
use dlds\mlm\kernel\interfaces\queries\MlmRewardQueryInterface;
use dlds\mlm\kernel\interfaces\queries\MlmSubjectQueryInterface;
use yii\db\ActiveQuery;

/**
 * Class ParticipantQuery
 * @package dlds\mlm\app\models
 * @see RwdBasic
 */
class RwdBasicQuery extends ActiveQuery implements MlmRewardQueryInterface, MlmSubjectQueryInterface
{
    /**
     * @inheritdoc
     * @param MlmParticipantInterface $participant
     * @return $this
     */
    public function __mlmProfiteer(MlmParticipantInterface $participant)
    {
        $this->andWhere([RwdBasic::tableName() . '.usr_rewarded_id' => $participant->__mlmPrimaryKey()]);

        return $this;
    }

    /**
     * @inheritdoc
     * @param int $id
     * @param null $type
     * @return $this
     */
    public function __mlmSource($id, $type = null)
    {
        $this->andWhere([RwdBasic::tableName() . '.subject_id' => $id]);

        if ($type) {
            $this->andWhere([RwdBasic::tableName() . '.subject_type' => $type]);
        }

        return $this;
    }

    /**
     * @inheritdoc
     * @param bool $state
     * @return $this
     */
    public function __mlmPending($state = true)
    {
        $operand = $state ? '=' : '<>';

        $this->andWhere([$operand, RwdBasic::tableName() . '.status', 'pending']);

        return $this;
    }

    /**
     * @inheritdoc
     * @param bool $state
     * @return $this
     */
    public function __mlmApproved($state = true)
    {
        $operand = $state ? '=' : '<>';

        $this->andWhere([$operand, RwdBasic::tableName() . '.status', 'approved']);

        return $this;
    }

    /**
     * @inheritdoc
     * @param bool $state
     * @return $this
     */
    public function __mlmDenied($state = true)
    {
        $operand = $state ? '=' : '<>';

        $this->andWhere([$operand, RwdBasic::tableName() . '.status', 'denied']);

        return $this;
    }

    /**
     * @inheritdoc
     * @param bool $state
     * @return $this
     */
    public function __mlmPaid($state = true)
    {
        $operand = $state ? '=' : '<>';

        $this->andWhere([$operand, RwdBasic::tableName() . '.status', 'paid']);

        return $this;
    }

    /**
     * @inheritdoc
     * @param bool $state
     * @return $this
     */
    public function __mlmLocked($state = true)
    {
        $operand = $state ? '=' : '<>';

        $this->andWhere([$operand, RwdBasic::tableName() . '.status', 1]);

        return $this;
    }

    /**
     * @inheritdoc
     * @param bool $state
     * @return $this
     */
    public function __mlmFinal($state = true)
    {
        $operand = $state ? '=' : '<>';

        $this->andWhere([$operand, RwdBasic::tableName() . '.status', 1]);

        return $this;
    }

    /**
     * @inheritdoc
     * @param int $value
     * @param string $operator
     * @return $this
     */
    public function __mlmAge($value, $operator = self::OP_OLDER)
    {
        $birth = time() - $value;

        $this->andWhere([$operator, RwdBasic::tableName() . '.created_at', $birth]);

        return $this;
    }

    /**
     * @inheritdoc
     * @param integer|null $delay
     * @return $this
     */
    public function __mlmExpectingApproval($delay = null)
    {
        $this->__mlmAge($delay);

        $this->joinWith(['usrRewarded' => function ($q) {
            $q->__mlmEligibleToBasicRewards(true);
        }]);

        $this->__mlmPending(true);

        return $this;
    }

    /**
     * @inheritdoc
     * @param integer|null $delay
     * @return $this
     */
    public function __mlmExpectingDeny($delay = null)
    {
        $this->__mlmAge($delay);

        $this->joinWith(['usrRewarded' => function ($q) {
            $q->__mlmEligibleToBasicRewards(false);
        }]);

        $this->__mlmPending(true);

        return $this;
    }

    // <editor-fold defaultstate="collapsed" desc="MlmSubjectQueryInterface methods">

    /**
     * Queries subjects based on given participant interface
     * @param MlmParticipantInterface $participant
     * @return MlmSubjectQueryInterface
     */
    public function __mlmOwner(MlmParticipantInterface $participant)
    {
        $this->andWhere(['participant_id' => $participant->__mlmPrimaryKey()]);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function __mlmExpectingRewards()
    {
        // only rewards without extra rewards are expecting
        $this->joinWith([
            'rwdExtras' => function (ActiveQuery $q) {
                QueryHelper::relAlias($q, 're');
            },
        ]);

        $this->andWhere(['or',
            ['re.id' => null],
        ]);

        // only approved/paid rewards are expecting another (extra) rewards
        $this->andWhere([RwdBasic::tableName() . '.status' => ['approved']]);

        // only NOT final rewards are expecting another (extra) rewards
        $this->andWhere([RwdBasic::tableName() . '.is_final' => 0]);

        return $this;
    }
    // </editor-fold>

}

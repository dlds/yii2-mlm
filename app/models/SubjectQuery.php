<?php

/**
 * @author     Jirka Svoboda <code@svobik.com>
 * @copyright  2017 © svobik.com
 * @license    https://www.svobik.com/license.md
 * @timestamp  31/05/2017 13:26
 */

namespace dlds\mlm\app\models;

use dlds\mlm\app\helpers\QueryHelper;
use dlds\mlm\kernel\interfaces\MlmParticipantInterface;
use dlds\mlm\kernel\interfaces\queries\MlmSubjectQueryInterface;
use yii\db\ActiveQuery;

/**
 * Class ParticipantQuery
 * @package dlds\mlm\app\models
 */
class SubjectQuery extends ActiveQuery implements MlmSubjectQueryInterface
{

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
        $this->joinWith([
            'rwdBasic' => function (ActiveQuery $q) {
                QueryHelper::relAlias($q, 'rb');
            },
            'rwdCustom' => function (ActiveQuery $q) {
                QueryHelper::relAlias($q, 'rc');
            },
        ]);

        $this->andWhere(['or',
            ['rb.id' => null],
            ['rc.id' => null],
        ]);

        return $this;
    }
}

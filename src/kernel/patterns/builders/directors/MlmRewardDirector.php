<?php

/**
 * @author     Jirka Svoboda <code@svobik.com>
 * @copyright  2017 © svobik.com
 * @license    https://www.svobik.com/license.md
 * @timestamp  13/06/2017 09:11
 */

namespace dlds\mlm\kernel\patterns\builders\directors;

use dlds\mlm\helpers\MlmRewardHelper;
use dlds\mlm\kernel\interfaces\MlmParticipantInterface;
use dlds\mlm\kernel\interfaces\MlmSubjectInterface;
use dlds\mlm\kernel\patterns\builders\interfaces\MlmRewardBuilderInterface;
use dlds\mlm\kernel\patterns\builders\MlmRewardBasicBuilder;
use dlds\mlm\kernel\patterns\builders\MlmRewardCustomBuilder;
use dlds\mlm\kernel\patterns\builders\MlmRewardExtraBuilder;

/**
 * Class MlmRewardDirector
 * ---
 * Handles reward building.
 * ---
 * @package dlds\mlm\kernel\patterns\builders\directors
 * @see MlmRewardBasicBuilder
 * @see MlmRewardExtraBuilder
 * @see MlmRewardCustomBuilder
 */
class MlmRewardDirector
{
    /**
     * @var MlmRewardDirector
     */
    private static $_instance = null;

    /**
     * Creates new instance
     * @return MlmRewardDirector
     */
    public static function instance()
    {
        if (null === static::$_instance) {
            static::$_instance = new MlmRewardDirector();
        }

        return static::$_instance;
    }

    /**
     * MlmRewardBasicBuilder constructor.
     */
    private function __construct()
    {
        // silent
    }

    /**
     * Lets builder to build reward
     * @param MlmRewardBuilderInterface $builder
     * @param MlmParticipantInterface $profiteer
     * @return bool|\dlds\mlm\kernel\interfaces\MlmRewardInterface
     */
    public function build(MlmRewardBuilderInterface $builder, MlmParticipantInterface $profiteer)
    {
        $builder->init($profiteer);

        $builder->setParticipant();
        $builder->setSubject();

        // sets relations & level & value
        $builder->setReward();

        // sets default status
        $builder->setStatus();

        $builder->setIsLocked();
        $builder->setIsFinal();

        return $builder->result();
    }
}
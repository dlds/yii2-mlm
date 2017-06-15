<?php

/**
 * @author     Jirka Svoboda <code@svobik.com>
 * @copyright  2017 © svobik.com
 * @license    https://www.svobik.com/license.md
 * @timestamp  31/05/2017 10:39
 */

namespace dlds\mlm\kernel\exceptions;

use dlds\mlm\kernel\interfaces\MlmRewardInterface;
use dlds\mlm\kernel\patterns\builders\interfaces\MlmRewardBuilderInterface;
use yii\base\Exception;

class MlmRewardBuilderError extends Exception
{
    /**
     * @var MlmRewardBuilderInterface
     */
    private $_builder;

    /**
     * Attaches failed builder
     * @param MlmRewardBuilderInterface $builder
     */
    public function setBuilder(MlmRewardBuilderInterface $builder)
    {
        $this->_builder = $builder;
    }

    /**
     * Retrieves attached builder
     * @return MlmRewardBuilderInterface
     */
    public function getBuilder()
    {
        return $this->_builder;
    }

    /**
     * Retrieves errors
     * @param boolean $serialized
     * @return array
     */
    public function getErrors($serialized = false)
    {
        if (!$this->_builder) {
            return [];
        }

        $result = $this->_builder->result();

        if (!$result) {
            return 'No builder result retrieved';
        }

        if ($serialized) {
            return serialize($result->getErrors());
        }

        return $result->getErrors();
    }

    /**
     * Simple factory method handles exception creation
     * ---
     * @param MlmRewardBuilderInterface $builder
     * @return MlmRewardBuilderError
     */
    public static function factory(MlmRewardBuilderInterface $builder, $message)
    {
        $instance = new MlmRewardBuilderError($message);

        $instance->setBuilder($builder);

        return $instance;
    }
}
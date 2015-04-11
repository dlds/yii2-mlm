<?php
/**
 * @link http://www.digitaldeals.cz/
 * @copyright Copyright (c) 2014 Digital Deals s.r.o.
 * @license http://www.digitaldeals.cz/license/
 */

namespace dlds\mlm;

/**
 * This is the main class of the dlds\mlm component that should be registered as an application component.
 *
 * @author Jiri Svoboda <jiri.svobodao@dlds.cz>
 * @package mlm
 */
class Component extends \yii\base\Component {
    /**
     * @var string recipient classname
     */
    public $recipient;

    /**
     * @var int Indicates maximum tree depth limit commissions will affect (this level is reached conditionaly)
     */
    public $levelMax = 10;

    /**
     * @var int Indicates defualt tree depth limit commissions will affect (this level is reached everytime)
     */
    public $levelDefault = 6;

    /**
     * @var array defines level commissions percentage
     */
    public $levelCommissions = [
        1 => 25,
        6 => 5,
        7 => 1.03,
        8 => 0.77,
        9 => 0.52,
        10 => 0.26,
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        
    }

    /**
     * Retrieves commision percents based on given level
     * @param int $level
     * @return float commission percentage
     */
    public function getLevelCommission($level)
    {
        foreach (ksort($this->levelCommissions) as $maxlevel => $percentage)
        {
            if ($level <= $maxlevel)
            {
                return $percentage;
            }
        }

        return 0;
    }
}
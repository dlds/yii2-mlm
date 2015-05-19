<?php
/**
 * @link http://www.digitaldeals.cz/
 * @copyright Copyright (c) 2014 Digital Deals s.r.o.
 * @license http://www.digitaldeals.cz/license/
 */

namespace dlds\mlm\components;

/**
 * Formatter main class.
 *
 * @author Jiri Svoboda <jiri.svobodao@dlds.cz>
 * @package mlm
 */
class MlmFormatter extends \yii\i18n\Formatter {

    /**
     * @param string $value
     * @return string
     */
    public function asRoundedCurrency($value)
    {
        return \Yii::$app->formatter->asCurrency($value);
    }

    /**
     * @param string $value
     * @return string
     */
    public function asFloorCurrency($value, $decimals = 4, $locale = 'cs_CZ')
    {
        setlocale(LC_MONETARY, $locale);
        return money_format('%.'.$decimals.'n', $value);
    }
}
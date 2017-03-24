<?php

namespace dlds\mlm\components\interfaces;

interface MlmSubjectInterface
{
    /**
     * Retrieves available amount for rewards
     * ---
     * @param boolean $incVat
     * @return mixed
     */
    public function mlmSubjectAmount($incVat = false);

    /**
     * Retrieves vat
     * ---
     * When 21% is used than 0.21 should be retrieved if $asDecimal is true
     * ---
     * @param boolean $asDecimal
     * @return mixed
     */
    public function mlmSubjectVat($asDecimal = true);
}

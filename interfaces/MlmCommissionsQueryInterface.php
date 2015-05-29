<?php

namespace dlds\mlm\interfaces;

interface MlmCommissionsQueryInterface {

    /**
     * Filters by given type
     */
    public function hasType($type);

    /**
     * Filters by given status
     */
    public function hasStatus($status);

    /**
     * Filters by given identity
     */
    public function hasIdentity($identity);

    /**
     * Filters by given ticket order
     */
    public function hasTicketOrder($appTicketOrder);
}
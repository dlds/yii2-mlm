<?php

namespace dlds\mlm\components\interfaces;

interface MlmParticipantInterface
{

    public function mlmAncestor();

    public static function mlmMainParticipant();
}

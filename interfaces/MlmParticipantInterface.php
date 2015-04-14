<?php

namespace dlds\mlm\interfaces;

interface MlmParticipantInterface {

    public function getTreeDepth();

    public function getQueryJuniors($depth = null, $profitable);

    public function getQuerySeniors($depth = null);

    public function getPersonalCommission();

    public function canTakeCommission($level);
}
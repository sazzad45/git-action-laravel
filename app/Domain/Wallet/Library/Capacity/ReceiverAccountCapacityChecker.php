<?php

namespace App\Domain\Wallet\Library\Capacity;

use App\Traits\ApiResponseTrait;

class ReceiverAccountCapacityChecker
{
    use ApiResponseTrait;

    private $hasErrors;
    private $errorMessage;
    private ReceiverAccountCapacityCheckerParam $params;

    public function __construct(ReceiverAccountCapacityCheckerParam $params)
    {
        $this->hasErrors = false;
        $this->errorMessage = [];
        $this->params = $params;
    }

    private function processStart()
    {
        if ($this->params->getReceiverAccountCapacity()) {
            $this->checkIfReceiverAccountCapacityCrossed();
        }
    }

    private function checkIfReceiverAccountCapacityCrossed() {
        if ( ($this->params->getReceiverCurrentBalance() + $this->params->getTrxAmount()) > $this->params->getReceiverAccountCapacity()->amount ) {
            $this->hasErrors = true;
            $this->errorMessage[] = $this->params->getErrorMsgForAccCapacityExceed();
        }
    }

    public function check()
    {
        $this->processStart();
        return $this;
    }

    public function limitCrossed()
    {
        if ($this->hasErrors) {
            return $this->invalidResponse($this->errorMessage);
        }

        return false;
    }
}

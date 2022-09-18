<?php

namespace Sweikenb\Bundle\Contracts\Service;

use Sweikenb\Bundle\Contracts\Exceptions\ContractValidationException;
use Sweikenb\Bundle\Contracts\Exceptions\LockfileException;
use Sweikenb\Bundle\Contracts\Exceptions\StateException;

class ValidatorService
{
    private ScannerService $scannerService;
    private LockfileService $lockfileService;
    private FailureHandlerService $failureHandlerService;

    public function __construct(
        ScannerService $scannerService,
        LockfileService $lockfileService,
        FailureHandlerService $failureHandlerService
    ) {
        $this->scannerService = $scannerService;
        $this->lockfileService = $lockfileService;
        $this->failureHandlerService = $failureHandlerService;
    }

    /**
     * @throws ContractValidationException
     * @throws LockfileException
     * @throws StateException
     */
    public function execute(): bool
    {
        // get the lockfile state, if we have no state assume the validation was successful
        $expectedState = $this->lockfileService->load();
        if ($expectedState === null) {
            return true;
        }

        // get the current state
        $currentState = $this->scannerService->execute();

        // compare states
        if ($expectedState->getHash() !== $currentState->getHash()) {
            return $this->failureHandlerService->execute(
                'Unexpected contract changes detected! Execute "contracts:diff" for further details.'
            );
        }

        return true;
    }
}

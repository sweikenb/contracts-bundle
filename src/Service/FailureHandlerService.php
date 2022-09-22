<?php

namespace Sweikenb\Bundle\Contracts\Service;

use Sweikenb\Bundle\Contracts\DependencyInjection\Configuration;
use Sweikenb\Bundle\Contracts\Exceptions\ContractValidationException;

class FailureHandlerService
{
    private string $failAction;

    public function __construct(string $failAction)
    {
        $this->failAction = $failAction;
    }

    /**
     * @throws ContractValidationException
     */
    public function execute(string $message): bool
    {
        if ($this->failAction === Configuration::ACTION_FAIL) {
            throw new ContractValidationException(sprintf("CONTRACTS VALIDATION ERROR:\n%s", $message));
        }

        if ($this->failAction === Configuration::ACTION_WARNING) {
            fwrite(STDERR, sprintf("CONTRACTS VALIDATION WARNING:\n%s\n", $message));
        }

        return false;
    }
}

<?php

namespace Sweikenb\Bundle\Contracts\Service\Triggers;

use Sweikenb\Bundle\Contracts\DependencyInjection\Configuration;
use Sweikenb\Bundle\Contracts\Exceptions\ContractValidationException;
use Sweikenb\Bundle\Contracts\Exceptions\LockfileException;
use Sweikenb\Bundle\Contracts\Exceptions\StateException;
use Sweikenb\Bundle\Contracts\Service\ValidatorService;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;

/*
 * NOTE: This whole class is a workaround for the fact that symfony does not provide a dedicated cache:clear event.
 */

class CacheTriggerService implements CacheClearerInterface
{
    private ValidatorService $validatorService;
    private array $triggerConfig;
    private string $currentAppEnv;

    public function __construct(ValidatorService $validatorService, array $triggerConfig, string $currentAppEnv)
    {
        $this->validatorService = $validatorService;
        $this->triggerConfig = $triggerConfig;
        $this->currentAppEnv = $currentAppEnv;
    }

    /**
     * @throws ContractValidationException
     * @throws StateException
     * @throws LockfileException
     */
    public function clear(string $cacheDir)
    {
        $triggerKey = sprintf("%s:%s", Configuration::TRIGGER_CACHE_CLEAR, $this->currentAppEnv);
        if (in_array($triggerKey, $this->triggerConfig)) {
            $this->validatorService->execute();
        }
    }
}

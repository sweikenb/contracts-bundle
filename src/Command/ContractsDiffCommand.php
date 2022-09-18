<?php

namespace Sweikenb\Bundle\Contracts\Command;

use Sweikenb\Bundle\Contracts\Exceptions\LockfileException;
use Sweikenb\Bundle\Contracts\Exceptions\StateException;
use Sweikenb\Bundle\Contracts\Model\Factory\StateModelFactory;
use Sweikenb\Bundle\Contracts\Service\LockfileService;
use Sweikenb\Bundle\Contracts\Service\ScannerService;
use Sweikenb\Bundle\Contracts\Service\StateService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ContractsDiffCommand extends Command
{
    public const CMD_NAME = 'contracts:diff';

    private LockfileService $lockfileService;
    private ScannerService $scannerService;
    private StateService $stateService;
    private StateModelFactory $stateModelFactory;

    public function __construct(
        LockfileService $lockfileService,
        ScannerService $scannerService,
        StateService $stateService,
        StateModelFactory $stateModelFactory,
        string $name = null
    ) {
        parent::__construct($name);
        $this->lockfileService = $lockfileService;
        $this->scannerService = $scannerService;
        $this->stateService = $stateService;
        $this->stateModelFactory = $stateModelFactory;
    }

    protected function configure()
    {
        $this->setName(self::CMD_NAME);
        $this->setDescription('Compares the current state with the state in the lock-file.');
    }

    /**
     * @throws StateException
     * @throws LockfileException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $lockfileState = $this->lockfileService->load();
        if ($lockfileState === null) {
            // fake an empty state, so we can at least print information of new contracts
            $lockfileState = $this->stateModelFactory->create([]);
        }

        $this->stateService->diff($io, $lockfileState, $this->scannerService->execute());

        return self::SUCCESS;
    }
}

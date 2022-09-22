<?php

namespace Sweikenb\Bundle\Contracts\Command;

use Sweikenb\Bundle\Contracts\Exceptions\LockfileException;
use Sweikenb\Bundle\Contracts\Exceptions\StateException;
use Sweikenb\Bundle\Contracts\Model\Factory\StateModelFactory;
use Sweikenb\Bundle\Contracts\Service\LockfileService;
use Sweikenb\Bundle\Contracts\Service\ScannerService;
use Sweikenb\Bundle\Contracts\Service\StateDiffService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ContractsLockCommand extends Command
{
    public const CMD_NAME = 'sweikenb:contracts:lock';
    public const OPT_FORCE = 'force';

    private LockfileService $lockfileService;
    private ScannerService $scannerService;
    private StateDiffService $stateDiffService;
    private StateModelFactory $stateModelFactory;

    public function __construct(
        LockfileService $lockfileService,
        ScannerService $scannerService,
        StateDiffService $stateDiffService,
        StateModelFactory $stateModelFactory,
        string $name = null
    ) {
        parent::__construct($name);
        $this->lockfileService = $lockfileService;
        $this->scannerService = $scannerService;
        $this->stateDiffService = $stateDiffService;
        $this->stateModelFactory = $stateModelFactory;
    }

    protected function configure()
    {
        $this->setName(self::CMD_NAME);
        $this->setDescription('Locks the current contracts state.');
        $this->addOption(
            self::OPT_FORCE,
            null,
            InputOption::VALUE_NONE,
            'Force lock-update and ignore warnings.'
        );
    }

    /**
     * @throws StateException
     * @throws LockfileException
     * @throws \JsonException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $lockfileState = $this->lockfileService->load();
        if ($lockfileState === null) {
            // fake an empty state, so we can at least print information of new contracts
            $lockfileState = $this->stateModelFactory->create([]);
        }

        $currentState = $this->scannerService->execute();
        $diffSuccess = $this->stateDiffService->execute($io, $lockfileState, $currentState);

        if (!$diffSuccess && !$input->getOption(self::OPT_FORCE)) {
            $io->caution(
                'Aborting lockfile update due to warnings, add the "--force" flag to this to command to accept changes.'
            );
            return self::FAILURE;
        }

        $this->lockfileService->persist($currentState);

        $io->success('Contracts-lock updated.');
        return self::SUCCESS;
    }
}

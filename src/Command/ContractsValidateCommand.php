<?php

namespace Sweikenb\Bundle\Contracts\Command;

use Sweikenb\Bundle\Contracts\Exceptions\ContractValidationException;
use Sweikenb\Bundle\Contracts\Exceptions\LockfileException;
use Sweikenb\Bundle\Contracts\Exceptions\StateException;
use Sweikenb\Bundle\Contracts\Service\ValidatorService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ContractsValidateCommand extends Command
{
    public const CMD_NAME = 'contracts:validate';
    private ValidatorService $validatorService;

    public function __construct(ValidatorService $validatorService, string $name = null)
    {
        parent::__construct($name);
        $this->validatorService = $validatorService;
    }

    protected function configure()
    {
        $this->setName(self::CMD_NAME);
        $this->setDescription('Ensures the current state represents the expected (locked) state.');
    }

    /**
     * @throws ContractValidationException
     * @throws LockfileException
     * @throws StateException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($this->validatorService->execute()) {
            $io->success('Validation successful');
            return self::SUCCESS;
        }

        // This is just a fallback, if the validation failed the validator service is expected to throw an exception
        // so this part should not be reached if everything is working correctly.
        $io->error('Validation failed for unknown reason.');
        return self::FAILURE;
    }
}

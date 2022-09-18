<?php

namespace Sweikenb\Bundle\Contracts\Service;

use Sweikenb\Bundle\Contracts\Definition\PrivateContract;
use Sweikenb\Bundle\Contracts\Definition\PublicContract;
use Sweikenb\Bundle\Contracts\Exceptions\StateException;
use Sweikenb\Bundle\Contracts\Model\Factory\StateModelFactory;
use Sweikenb\Bundle\Contracts\Model\StateModel;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

class StateService
{
    private StateModelFactory $stateModelFactory;

    public function __construct(StateModelFactory $stateModelFactory)
    {
        $this->stateModelFactory = $stateModelFactory;
    }

    /**
     * @throws StateException
     */
    public function execute(Finder $files): StateModel
    {
        $pattern = sprintf(
            '#(@PublicContract|@%s|@PrivateContract|@%s)\((\d*)\)#',
            str_replace('\\', '\\\\', ltrim(PublicContract::class, '\\')),
            str_replace('\\', '\\\\', ltrim(PrivateContract::class, '\\'))
        );

        $state = [];
        foreach ($files as $file) {
            $content = $file->getContents();
            if (preg_match($pattern, $content, $matches)) {
                $version = max(1, (int)$matches[2]);
                $state[$file->getRelativePathname()] = [sha1($content), $version];
            }
        }

        return $this->stateModelFactory->create($state);
    }

    public function diff(SymfonyStyle $io, StateModel $expectedState, StateModel $actualState): bool
    {
        $expected = $expectedState->toArray();
        $actual = $actualState->toArray();

        $io->writeln('Starting to validate lock-file against current state ...');

        $warnings = [];
        $unchanged = [];
        $new = [];
        $updated = [];

        $io->progressStart(max(count($expected), count($actual)));
        foreach ($expected as $file => $info) {
            $io->progressAdvance();
            if (!isset($actual[$file])) {
                $warnings[] = sprintf('The file "%s" is not present anymore.', $file);
                continue;
            }

            [$expHash, $expVersion] = $info;
            [$actHash, $actVersion] = $actual[$file];

            if ($expHash !== $actHash) {
                if ($expVersion < $actVersion) {
                    $updated[] = $file;
                } else {
                    $warnings[] = sprintf(
                        'The content of file "%s" has changed without specifying a new contract version.',
                        $file
                    );
                }
            } else {
                $unchanged[] = $file;
            }
        }
        foreach ($actual as $file => $info) {
            if (!isset($expected[$file])) {
                $io->progressAdvance();
                $new[] = $file;
            }
        }
        $io->progressFinish();

        if (!empty($unchanged)) {
            $io->writeln(sprintf("<info>Unchanged</info> contracts (%d):", count($unchanged)));
            array_map(fn($file) => $io->writeln(sprintf("> %s", $file)), $unchanged);
        }

        if (!empty($updated)) {
            $io->writeln(sprintf("<info>Updated</info> contracts (%d):", count($updated)));
            array_map(fn($file) => $io->writeln(sprintf("> %s", $file)), $updated);
        }

        if (!empty($new)) {
            $io->writeln(sprintf("<info>New</info> contracts (%d):", count($new)));
            array_map(fn($file) => $io->writeln(sprintf("> %s", $file)), $new);
        }

        if (!empty($warnings)) {
            $io->caution(sprintf('%d warnings detected:', count($warnings)));
            array_map(fn($file) => $io->writeln(sprintf("> <comment>%s</comment>", $file)), $warnings);
            return false;
        }

        $io->success('Your contracts are intact.');
        return true;
    }
}

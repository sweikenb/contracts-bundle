<?php

namespace Sweikenb\Bundle\Contracts\Service;

use Sweikenb\Bundle\Contracts\Model\StateModel;
use Symfony\Component\Console\Style\SymfonyStyle;

class StateDiffService
{
    public function execute(SymfonyStyle $io, StateModel $expectedState, StateModel $actualState): bool
    {
        // get data and remove meta fields
        $expected = $expectedState->toArray();
        $actual = $actualState->toArray();
        unset($expected['_'], $actual['_']);

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
            if ($info['hash'] !== $actual[$file]['hash']) {
                $this->renderInfoDiff(
                    $info['public_version'],
                    $actual[$file]['public_version'],
                    'public',
                    $file,
                    $info,
                    $actual[$file],
                    $updated,
                    $warnings
                );
                $this->renderInfoDiff(
                    $info['private_version'],
                    $actual[$file]['private_version'],
                    'private',
                    $file,
                    $info,
                    $actual[$file],
                    $updated,
                    $warnings
                );
            } else {
                $unchanged[] = $this->renderFileForDiff($file, $actual[$file], $info);
            }
        }
        foreach ($actual as $file => $info) {
            if (!isset($expected[$file])) {
                $io->progressAdvance();
                $new[] = $this->renderFileForDiff($file, $info, []);
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

        return true;
    }

    private function renderFileForDiff(string $file, array $actual, array $lockfile): string
    {
        $publicVersionOld = ($lockfile['public_version'] ?? false) ?: 'false';
        $publicVersionNew = ($actual['public_version'] ?? false) ?: 'false';
        if ($publicVersionOld !== $publicVersionNew) {
            $publicVersionNew = sprintf(
                '<comment>%s</comment> -> <info>%s</info>',
                $publicVersionOld,
                $publicVersionNew
            );
        } else {
            $publicVersionNew = sprintf('<comment>%s</comment>', $publicVersionNew);
        }

        $privateVersionOld = ($lockfile['private_version'] ?? false) ?: 'false';
        $privateVersionNew = ($actual['private_version'] ?? false) ?: 'false';
        if ($privateVersionOld !== $privateVersionNew) {
            $privateVersionNew = sprintf(
                '<comment>%s</comment> --> <info>%s</info>',
                $privateVersionOld,
                $privateVersionNew
            );
        } else {
            $privateVersionNew = sprintf('<comment>%s</comment>', $privateVersionNew);
        }

        return sprintf(
            '%s => public: %s, private: %s',
            $file,
            $publicVersionNew,
            $privateVersionNew,
        );
    }

    /**
     * @param bool|int $expected
     * @param bool|int $actual
     * @param string $contractType
     * @param string $file
     * @param array $expectedInfo
     * @param array $actualInfo
     * @param array $updated
     * @param array $warnings
     *
     * @return void
     */
    private function renderInfoDiff(
        $expected,
        $actual,
        string $contractType,
        string $file,
        array $expectedInfo,
        array $actualInfo,
        array &$updated,
        array &$warnings
    ): void {
        if ($expected !== $actual) {
            if (is_int($expected) && is_int($actual)) {
                if ($expected < $actual) {
                    $updated[] = $this->renderFileForDiff($file, $actualInfo, $expectedInfo);
                } else {
                    $warnings[] = sprintf(
                        'The content of file "%s" has changed without specifying a new %s-contract version.',
                        $file,
                        $contractType
                    );
                }
            } else {
                $warnings[] = sprintf(
                    'The %s-contract of "%s" has changed from "%s" to "%s"',
                    $contractType,
                    $file,
                    $expected ?: 'false',
                    $actual ?: 'false'
                );
            }
        }
    }
}

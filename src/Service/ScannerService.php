<?php

namespace Sweikenb\Bundle\Contracts\Service;

use Sweikenb\Bundle\Contracts\Definition\PrivateContract;
use Sweikenb\Bundle\Contracts\Definition\PublicContract;
use Sweikenb\Bundle\Contracts\Exceptions\StateException;
use Sweikenb\Bundle\Contracts\Model\Factory\FinderFactory;
use Sweikenb\Bundle\Contracts\Model\StateModel;

class ScannerService
{
    private FinderFactory $finderFactory;
    private StateService $stateService;
    private array $scanDirs;
    private array $scanFilePatterns;
    private array $scanIgnoreFilePattern;

    public function __construct(
        FinderFactory $finderFactory,
        StateService $stateService,
        array $scanDirs,
        array $scanFilePattern,
        array $scanIgnoreFilePattern
    ) {
        $this->finderFactory = $finderFactory;
        $this->stateService = $stateService;
        $this->scanDirs = array_map(fn($path) => sprintf("%s", rtrim($path, '/')), $scanDirs);
        $this->scanFilePatterns = $scanFilePattern;
        $this->scanIgnoreFilePattern = $scanIgnoreFilePattern;
    }

    /**
     * @throws StateException
     */
    public function execute(): StateModel
    {
        $containsPatterns = [
            ltrim(PublicContract::class, '\\'),
            ltrim(PrivateContract::class, '\\'),
        ];

        $finder = $this->finderFactory->create();
        $finder
            ->files()
            ->in($this->scanDirs)
            ->ignoreVCS(true)
            ->ignoreVCSIgnored(true)
            ->ignoreUnreadableDirs()
            ->followLinks()
            ->name($this->scanFilePatterns)
            ->notName($this->scanIgnoreFilePattern)
            ->contains($containsPatterns);

        return $this->stateService->execute($finder);
    }
}

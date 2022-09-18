<?php

namespace Sweikenb\Bundle\Contracts\Service;

use Sweikenb\Bundle\Contracts\Exceptions\LockfileException;
use Sweikenb\Bundle\Contracts\Exceptions\StateException;
use Sweikenb\Bundle\Contracts\Model\Factory\StateModelFactory;
use Sweikenb\Bundle\Contracts\Model\StateModel;

class LockfileService
{
    private StateModelFactory $stateModelFactory;
    private string $lockfile;
    private ?StateModel $currentState = null;

    public function __construct(StateModelFactory $stateModelFactory, string $lockfile)
    {
        $this->stateModelFactory = $stateModelFactory;
        $this->lockfile = $lockfile;
    }

    /**
     * @throws LockfileException
     * @throws StateException
     */
    public function load(): ?StateModel
    {
        if ($this->currentState === null) {
            if (!file_exists($this->lockfile)) {
                return null;
            }
            if (!is_readable($this->lockfile)) {
                throw new LockfileException('Contracts lockfile not readable.');
            }

            $content = (string)@file_get_contents($this->lockfile);
            $hash = mb_substr($content, 0, 40);
            $content = trim(mb_substr($content, 40));
            if ($hash !== sha1($content)) {
                throw new LockfileException('Contracts lockfile corrupted.');
            }

            $content = @json_decode($content, true);
            if (is_array($content)) {
                $this->currentState = $this->stateModelFactory->create($content);
            }
        }
        return $this->currentState;
    }

    /**
     * @throws LockfileException
     */
    public function persist(StateModel $state): bool
    {
        $content = json_encode($state->toArray(), JSON_FORCE_OBJECT);
        $hash = sha1($content);
        if (!file_put_contents($this->lockfile, sprintf("%s\n%s", $hash, $content))) {
            throw new LockfileException('Contracts lockfile could not be persisted.');
        }
        $this->currentState = $state;
        return true;
    }

    /**
     * @return $this
     */
    public function flushCache(): self
    {
        $this->currentState = null;
        return $this;
    }
}

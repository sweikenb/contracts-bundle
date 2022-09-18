<?php

namespace Sweikenb\Bundle\Contracts\Model;

class StateModel
{
    /**
     * @var array
     */
    private array $state;

    public function __construct(array $state)
    {
        ksort($state);
        $this->state = $state;
    }

    public function toArray(): array
    {
        return $this->state;
    }

    public function getHash(): string
    {
        return sha1(serialize($this->state));
    }
}

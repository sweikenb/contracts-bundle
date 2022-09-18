<?php

namespace Sweikenb\Bundle\Contracts\Abstracts;

abstract class AbstractContract
{
    protected int $version;

    public function __construct(int $version = 1)
    {
        $this->version = max(1, $version);
    }

    public function getVersion(): int
    {
        return $this->version;
    }
}

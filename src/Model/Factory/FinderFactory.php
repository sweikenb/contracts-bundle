<?php

namespace Sweikenb\Bundle\Contracts\Model\Factory;

use Symfony\Component\Finder\Finder;

class FinderFactory
{
    public function create(): Finder
    {
        return new Finder();
    }
}

<?php

namespace Sweikenb\Bundle\Contracts\Model\Factory;

use Sweikenb\Bundle\Contracts\Exceptions\StateException;
use Sweikenb\Bundle\Contracts\Model\StateModel;
use Sweikenb\Bundle\Contracts\Service\Parser\State\Version1;

class StateModelFactory
{
    private Version1 $v1Parser;

    public function __construct(Version1 $v1Parser)
    {
        $this->v1Parser = $v1Parser;
    }

    /**
     * @throws StateException
     */
    public function create(array $state): StateModel
    {
        switch ($state['_']['version'] ?? '') {
            case Version1::KEY:
            default:
                $state = $this->v1Parser->execute($state);
                break;
        }
        return new StateModel($state);
    }
}

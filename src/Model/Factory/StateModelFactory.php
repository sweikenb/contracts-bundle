<?php

namespace Sweikenb\Bundle\Contracts\Model\Factory;

use Sweikenb\Bundle\Contracts\Exceptions\StateException;
use Sweikenb\Bundle\Contracts\Model\StateModel;
use Sweikenb\Bundle\Contracts\Service\StateService;

class StateModelFactory
{
    /**
     * @throws StateException
     */
    public function create(array $state): StateModel
    {
        // perform some basic checks, so we do not continue with broken data
        foreach ($state as $file => $info) {
            if (is_numeric($file) // rel-filepath
                || count($info) !== 2
                || strlen($info[0]) !== 40 // file hash
                || !is_numeric($info[1]) // contract version
            ) {
                throw new StateException('Can not create state model for invalid state data.');
            }
        }
        return new StateModel($state);
    }
}

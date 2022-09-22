<?php

namespace Sweikenb\Bundle\Contracts\Service\Parser\State;

use Sweikenb\Bundle\Contracts\Exceptions\StateException;

class Version1
{
    public const KEY = 'v1';

    /**
     * @throws StateException
     */
    public function execute(array $state): array
    {
        // perform some basic checks, so we do not continue with broken data
        foreach ($state as $file => $info) {
            if ($file === '_') {
                // skipp the meta field
                continue;
            }

            $hash = $info['hash'] ?? '';
            $publicVersion = $info['public_version'] ?? '';
            $privateVersion = $info['private_version'] ?? '';

            if (is_numeric($file)
                || mb_strlen($hash) !== 40
                || (!is_bool($publicVersion) && !is_numeric($publicVersion))
                || (!is_bool($privateVersion) && !is_numeric($privateVersion))
            ) {
                throw new StateException('Can not create state model for invalid state data.');
            }
        }
        return $state;
    }
}

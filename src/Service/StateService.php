<?php

namespace Sweikenb\Bundle\Contracts\Service;

use Sweikenb\Bundle\Contracts\Definition\PrivateContract;
use Sweikenb\Bundle\Contracts\Definition\PublicContract;
use Sweikenb\Bundle\Contracts\Exceptions\StateException;
use Sweikenb\Bundle\Contracts\Model\Factory\StateModelFactory;
use Sweikenb\Bundle\Contracts\Model\StateModel;
use Sweikenb\Bundle\Contracts\Service\Parser\State\Version1;
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

        $state = ['_' => ['version' => Version1::KEY]];
        foreach ($files as $file) {
            $content = $file->getContents();
            if (preg_match_all($pattern, $content, $matches)) {
                $publicVersion = false;
                $privateVersion = false;
                foreach ($matches[1] as $i => $match) {
                    if (mb_substr($match, -14) === 'PublicContract') {
                        $publicVersion = max(1, (int)$matches[2][$i]);
                    }
                    if (mb_substr($match, -15) === 'PrivateContract') {
                        $privateVersion = max(1, (int)$matches[2][$i]);
                    }
                }
                $state[$file->getRelativePathname()] = [
                    'hash' => sha1($content),
                    'public_version' => $publicVersion,
                    'private_version' => $privateVersion,
                ];
            }
        }
        ksort($state);

        return $this->stateModelFactory->create($state);
    }
}

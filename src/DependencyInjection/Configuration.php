<?php

namespace Sweikenb\Bundle\Contracts\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public const PREFIX = 'sweikenb_contracts';

    public const LOCKFILE = 'lockfile';
    public const ON_UNEXPECTED_CHANGE = 'on_unexpected_change';
    public const SCAN_DIRS = 'scan_dirs';
    public const SCAN_FILE_PATTERNS = 'scan_file_patterns';
    public const SCAN_IGNORE_FILE_PATTERNS = 'scan_ignore_file_patterns';
    public const TRIGGERS = 'triggers';

    public const ACTION_FAIL = 'fail';
    public const ACTION_WARNING = 'warning';

    public const TRIGGER_CACHE_CLEAR = 'cache_clear';

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('sweikenb_contracts');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode(self::LOCKFILE)
                    ->defaultValue('contracts.lock')
                ->end()
                ->enumNode(self::ON_UNEXPECTED_CHANGE)
                    ->values([self::ACTION_FAIL, self::ACTION_WARNING])
                    ->defaultValue(self::ACTION_FAIL)
                ->end()
                ->arrayNode(self::SCAN_DIRS)
                    ->scalarPrototype()->end()
                    ->defaultValue([
                        'bin',
                        'src',
                    ])
                ->end()
                ->arrayNode(self::SCAN_FILE_PATTERNS)
                    ->scalarPrototype()->end()
                    ->defaultValue([
                        '*.php'
                    ])
                ->end()
                ->arrayNode(self::SCAN_IGNORE_FILE_PATTERNS)
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode(self::TRIGGERS)
                    ->scalarPrototype()->end()
                    ->defaultValue([
                        self::TRIGGER_CACHE_CLEAR.':dev',
                        self::TRIGGER_CACHE_CLEAR.':test',
                    ])
                ->end()
            ->end();

        return $treeBuilder;
    }
}

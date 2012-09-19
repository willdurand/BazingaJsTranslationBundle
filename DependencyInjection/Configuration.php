<?php

namespace Bazinga\ExposeTranslationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * Configuration class.
 *
 * @package ExposeTranslationBundle
 * @subpackage DependencyInjection
 * @author William DURAND <william.durand1@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();

        $builder->root('bazinga_expose_translation')
            ->fixXmlConfig('default_domain')
            ->children()
                ->scalarNode('locale_fallback')->defaultValue('')->end()
                ->arrayNode('default_domains')
                    ->beforeNormalization()
                        ->ifTrue(function($v) { return !is_array($v); })
                        ->then(function($v) { return array($v); })
                    ->end()
                    ->prototype('scalar')->end()
                ->end()
            ->end();

        return $builder;
    }
}

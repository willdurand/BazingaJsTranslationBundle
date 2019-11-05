<?php

namespace Bazinga\Bundle\JsTranslationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * @author William DURAND <william.durand1@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder('bazinga_js_translation');

        // Keep compatibility with symfony/config < 4.2
        if (!method_exists($builder, 'getRootNode')) {
            $rootNode = $builder->root('bazinga_js_translation');
        } else {
            $rootNode = $builder->getRootNode();
        }

        $rootNode
            ->fixXmlConfig('active_locale')
            ->fixXmlConfig('active_domain')
            ->children()
                ->scalarNode('locale_fallback')->defaultValue('en')->end()
                ->scalarNode('default_domain')->defaultValue('messages')->end()
                ->scalarNode('http_cache_time')->defaultValue('86400')->end()
                ->arrayNode('active_locales')
                    ->prototype('scalar')
                    ->end()
                ->end()
                ->arrayNode('active_domains')
                    ->prototype('scalar')
                    ->end()
                ->end()
            ->end();

        return $builder;
    }
}

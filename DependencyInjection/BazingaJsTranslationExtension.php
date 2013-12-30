<?php

namespace Bazinga\Bundle\JsTranslationBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;

/**
 * @author William DURAND <william.durand1@gmail.com>
 */
class BazingaJsTranslationExtension extends Extension
{
    /**
     * Load configuration.
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor     = new Processor();
        $configuration = new Configuration($container->getParameter('kernel.debug'));
        $config = $processor->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
        $loader->load('controllers.xml');

        $container
            ->getDefinition('bazinga.jstranslation.controller')
            ->replaceArgument(5, $config['locale_fallback'])
            ->replaceArgument(6, $config['default_domain']);

        $container
            ->getDefinition('bazinga.jstranslation.translation_dumper')
            ->replaceArgument(4, $config['locale_fallback'])
            ->replaceArgument(5, $config['default_domain']);
    }
}

<?php

namespace Bazinga\ExposeTranslationBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;

/**
 * @author William DURAND <william.durand1@gmail.com>
 */
class BazingaExposeTranslationExtension extends Extension
{
    /**
     * Load configuration.
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration($container->getParameter('kernel.debug'));
        $config = $processor->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
        $loader->load('controllers.xml');

        $defaultDomains = array_merge($config['default_domains'], array('messages'));

        $container
            ->getDefinition('bazinga.exposetranslation.controller')
            ->replaceArgument(5, $config["locale_fallback"])
            ->replaceArgument(6, $defaultDomains);
    }
}

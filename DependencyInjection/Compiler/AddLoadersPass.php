<?php

namespace Bazinga\ExposeTranslationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author William DURAND <william.durand1@gmail.com>
 */
class AddLoadersPass implements CompilerPassInterface
{
    protected $container;
    protected $loaders = array();

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('bazinga.exposetranslation.controller')) {
            return;
        }

        $this->container = $container;

        foreach ($container->findTaggedServiceIds('translation.loader') as $loaderId => $attributes) {
            $this->registerLoader($loaderId);
        }

        $this->container->setParameter("bazinga.exposetranslation.loaders", $this->loaders);
    }

    protected function registerLoader($loaderId)
    {
        $split = explode('.', $loaderId);
        $id    = end($split);

        $this->loaders[$id] =  $loaderId;
    }
}

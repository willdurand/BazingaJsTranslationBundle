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
            $attributes = array_shift($attributes);

            $this->registerLoader($attributes['alias'], $loaderId);

            if (isset($attributes['legacy-alias'])) {
                $this->registerLoader($attributes['legacy-alias'], $loaderId);
            }
        }

        $this->container->setParameter("bazinga.exposetranslation.loaders", $this->loaders);
    }

    protected function registerLoader($alias, $loaderId)
    {
        $this->container
            ->getDefinition('bazinga.exposetranslation.controller')
            ->addMethodCall('addLoader', array($alias, new Reference($loaderId)));
    }
}

<?php

namespace Bazinga\ExposeTranslationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author William DURAND <william.durand1@gmail.com>
 */
class AddLoadersPass implements CompilerPassInterface
{
    protected $container;

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
    }

    protected function registerLoader($alias, $loaderId)
    {
        $split = explode('.', $loaderId);
        $id    = end($split);

        $this->container
            ->getDefinition('bazinga.exposetranslation.controller')
            ->addMethodCall('addLoader', array($id, new Reference($loaderId)));

        $this->container
            ->getDefinition('bazinga.exposetranslation.dumper.translation_dumper')
            ->addMethodCall('addLoader', array($id, new Reference($loaderId)));
    }
}

<?php

namespace Bazinga\ExposeTranslationBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Bazinga\ExposeTranslationBundle\DependencyInjection\Compiler\AddLoadersPass;

/**
 * BazingaExposeTranslationBundle class.
 *
 * @package ExposeTranslationBundle
 * @author William DURAND <william.durand1@gmail.com>
 */
class BazingaExposeTranslationBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AddLoadersPass());
    }
}

<?php

namespace Bazinga\Bundle\JsTranslationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\OutOfBoundsException;

/**
 * @author Hugo MONTEIRO <hugo.monteiro@gmail.com>
 */
class TranslationResourceFilesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('translator.default')) {
            return;
        }

        $translationFiles = $this->getTranslationFilesFromAddResourceCalls($container);
        $translationFiles = array_merge($translationFiles, $this->getTranslationFiles($container));

        $container->getDefinition('bazinga.jstranslation.translation_finder')->replaceArgument(0, $translationFiles);
    }

    private function getTranslationFilesFromAddResourceCalls(ContainerBuilder $container)
    {
        $translationFiles = array();

        $methodCalls = $container->findDefinition('translator.default')->getMethodCalls();
        foreach ($methodCalls as $methodCall) {
            if ($methodCall[0] === 'addResource') {
                $locale = $methodCall[1][2];
                $filename = $methodCall[1][1];

                if (!isset($translationFiles[$locale])) {
                    $translationFiles[$locale] = array();
                }

                $translationFiles[$locale][] = $filename;
            }
        }

        return $translationFiles;
    }

    private function getTranslationFiles(ContainerBuilder $container)
    {
        $translationFiles = array();
        $translatorDef = $container->findDefinition('translator.default');

        // Symfony 3.3 added the default locale as third argument, before the loader ids and the options
        if (is_string($translatorDef->getArgument(2))) {
            $optionsArgumentIndex = 4;
        } else {
            $optionsArgumentIndex = 3;
        }

        $translatorOptions = $translatorDef->getArgument($optionsArgumentIndex);
        
        if (isset($translatorOptions['resource_files'])) {
            $translationFiles = $translatorOptions['resource_files'];
        }

        return $translationFiles;
    }
}

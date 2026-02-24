<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $parameters = $container->parameters();
    $parameters->set('bazinga.jstranslation.translation_finder.class', \Bazinga\Bundle\JsTranslationBundle\Finder\TranslationFinder::class);
    $parameters->set('bazinga.jstranslation.translation_dumper.class', \Bazinga\Bundle\JsTranslationBundle\Dumper\TranslationDumper::class);

    $services = $container->services();

    $services->set('bazinga.jstranslation.translation_finder', '%bazinga.jstranslation.translation_finder.class%')
        ->public()
        ->args([[]]);

    $services->set('bazinga.jstranslation.translation_dumper', '%bazinga.jstranslation.translation_dumper.class%')
        ->public()
        ->args([
            service('twig'),
            service('bazinga.jstranslation.translation_finder'),
            service('filesystem'),
            abstract_arg('fallback locale'),
            abstract_arg('default domain'),
            abstract_arg('active locales'),
            abstract_arg('active domains'),
        ]);

    $services->set('bazinga.jstranslation.dump_command', \Bazinga\Bundle\JsTranslationBundle\Command\DumpCommand::class)
        ->public()
        ->args([
            service('bazinga.jstranslation.translation_dumper'),
            '%kernel.project_dir%',
        ])
        ->tag('console.command', ['command' => 'bazinga:js-translation:dump']);
};

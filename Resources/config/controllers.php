<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('bazinga.jstranslation.controller.class', \Bazinga\Bundle\JsTranslationBundle\Controller\Controller::class);

    $services->set('bazinga.jstranslation.controller', '%bazinga.jstranslation.controller.class%')
        ->public()
        ->args([
            service('translator'),
            service('twig'),
            service('bazinga.jstranslation.translation_finder'),
            '%kernel.cache_dir%/bazinga-js-translation',
            '%kernel.debug%',
            '', // fallback (locale)
            '', // default domain
            '', // http cache time
        ]);
};

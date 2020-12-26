<?php

if (!($loader = @include __DIR__ . '/../vendor/autoload.php')) {
    echo <<<EOT
You need to install the project dependencies using Composer:
$ wget http://getcomposer.org/composer.phar
OR
$ curl -s https://getcomposer.org/installer | php
$ php composer.phar install --dev
$ phpunit
EOT;

    exit(1);
}

if (version_compare(PHP_VERSION, '8.0.0', '<')) {
    libxml_disable_entity_loader(true);
}

$loader->add('Bazinga\Bundle\JsTranslationBundle\Tests', __DIR__);

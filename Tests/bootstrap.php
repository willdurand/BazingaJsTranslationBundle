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


if (\PHP_VERSION_ID >= 80000) {
    libxml_disable_entity_loader(true);
}

$loader->add('Bazinga\Bundle\JsTranslationBundle\Tests', __DIR__);

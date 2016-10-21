<?php

namespace Bazinga\Bundle\JsTranslationBundle\Extractor;

final class CoffeeExtractor extends Extractor
{
    protected $sequence = '\\.trans(?:Choice)?\\s';

    protected function getSupportedFileExtensions()
    {
        return array('coffee');
    }
}

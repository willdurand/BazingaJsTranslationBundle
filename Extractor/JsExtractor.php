<?php

namespace Bazinga\Bundle\JsTranslationBundle\Extractor;

final class JsExtractor extends Extractor
{
    protected $sequence = '\\.trans(?:Choice)?\\(';

    protected function getSupportedFileExtensions()
    {
        return array('js', 'jsx');
    }
}

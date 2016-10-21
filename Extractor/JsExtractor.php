<?php

namespace Bazinga\Bundle\JsTranslationBundle\Extractor;

final class JsExtractor extends Extractor
{
    protected $sequence = '\\.trans(?:Choice)?\\(';

    protected $supportedFileExtensions = [
        'js',
        'jsx',
    ];
}

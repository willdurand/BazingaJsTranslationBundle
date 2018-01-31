<?php

namespace Bazinga\JsTranslationBundle\Tests\Extractor;

use Symfony\Component\Filesystem\Filesystem;
use Bazinga\Bundle\JsTranslationBundle\Extractor\JsExtractor;

final class JsExtractorTest extends AbstractExtractorTest
{
    const TEST_LOCALE = 'en';
    const TEST_KEY_1 = 'test-key-1';

    public function setUp()
    {
        $this->extractor = new JsExtractor(
            new Filesystem()
        );
    }
}

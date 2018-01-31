<?php

namespace Bazinga\JsTranslationBundle\Tests\Extractor;

use Symfony\Component\Filesystem\Filesystem;
use Bazinga\Bundle\JsTranslationBundle\Extractor\CoffeeExtractor;

final class CoffeeExtractorTest extends AbstractExtractorTest
{
    const TEST_LOCALE = 'en';
    const TEST_KEY_1 = 'test-key-1';

    public function setUp()
    {
        $this->extractor = new CoffeeExtractor(
            new Filesystem()
        );
    }
}

<?php

namespace Bazinga\Bundle\JsTranslationBundle\Tests;

use Bazinga\Bundle\JsTranslationBundle\Util;
use PHPUnit\Framework\TestCase;

class UtilTest extends TestCase
{

    /**
     * @dataProvider provideExtractCatalogueInformationFromFilename
     */
    public function testExtractCatalogueInformationFromFilename(string $filename, array $expectedInformation): void
    {
        [$domain, $locale, $extension] = Util::extractCatalogueInformationFromFilename($filename);

        $this->assertSame($expectedInformation[0], $domain);
        $this->assertSame($expectedInformation[1], $locale);
        $this->assertSame($expectedInformation[2], $extension);
    }

    public function provideExtractCatalogueInformationFromFilename(): iterable
    {
        yield ['messages.en.yml', ['messages', 'en', 'yml']];
        yield ['messages.en.xliff', ['messages', 'en', 'xliff']];
        yield 'with ICU support' => ['messages+intl-icu.en.xliff', ['messages', 'en', 'xliff']];
    }
}

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
        yield ['messages.en.yml', ['messages', 'en', 'yml', null]];
        yield ['messages.en.xliff', ['messages', 'en', 'xliff', null]];
        yield ['messages+intl-icu.en.xliff', ['messages+intl-icu', 'en', 'xliff']];
    }

    /**
     * @dataProvider provideCleanDomain
     */
    public function testCleanDomain(string $domain, string $expectedCleanDomain): void
    {
        $this->assertSame($expectedCleanDomain, Util::cleanDomain($domain));
    }

    public function provideCleanDomain(): iterable
    {
        yield ['messages', 'messages'];
        yield ['messages+intl-icu', 'messages'];
    }
}

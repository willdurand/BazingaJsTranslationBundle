<?php

namespace Bazinga\JsTranslationBundle\Tests\Extractor;

use Bazinga\Bundle\JsTranslationBundle\Extractor\FrontendExtractor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Translation\MessageCatalogue;

final class FrontendExtractorTest extends TestCase
{
    const TEST_LOCALE = 'en';
    const TEST_KEY_1 = 'test-key-1';

    /**
     * @var FrontendExtractor
     */
    private $extractor;

    public function setUp()
    {
        $filesystem = new Filesystem();
        $this->extractor = new FrontendExtractor($filesystem, array('js', 'jsx', 'ts'), '\.trans(?:Choice)?\(');
    }

    public function testExtractShouldNotRetrieveTransKey()
    {
        $catalogue = new MessageCatalogue(self::TEST_LOCALE);
        $this->extractor->extract(__DIR__.'/../Fixtures/Extractor/NotValidTransFunctionUsage', $catalogue);
        $this->assertEmpty($catalogue->all());
    }

    public function testExtractShouldRetrieveTransKey()
    {
        $catalogue = new MessageCatalogue(self::TEST_LOCALE);
        $this->extractor->extract(__DIR__.'/../Fixtures/Extractor/ATransFunctionUsage', $catalogue);
        $this->assertTrue($catalogue->has(self::TEST_KEY_1));
    }

    public function testExtractShouldNotRetrieveTransChoiceKey()
    {
        $catalogue = new MessageCatalogue(self::TEST_LOCALE);
        $this->extractor->extract(__DIR__.'/../Fixtures/Extractor/NotValidTransChoiceFunctionUsage', $catalogue);
        $this->assertEmpty($catalogue->all());
    }

    public function testExtractShouldRetrieveTransChoiceKey()
    {
        $catalogue = new MessageCatalogue(self::TEST_LOCALE);
        $this->extractor->extract(__DIR__.'/../Fixtures/Extractor/ATransChoiceFunctionUsage', $catalogue);
        $this->assertTrue($catalogue->has(self::TEST_KEY_1));
    }
}

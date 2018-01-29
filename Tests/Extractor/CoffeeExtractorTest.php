<?php

namespace Bazinga\JsTranslationBundle\Tests\Extractor;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Translation\MessageCatalogue;
use Bazinga\Bundle\JsTranslationBundle\Extractor\CoffeeExtractor;

final class CoffeeExtractorTest extends TestCase
{
    const TEST_LOCALE = 'en';
    const TEST_KEY_1 = 'test-key-1';

    /**
     * @var CoffeeExtractor
     */
    private $extractor;

    public function setUp()
    {
        $this->extractor = new CoffeeExtractor(
            new Filesystem()
        );
    }

    /**
     * @dataProvider resourcesWithNotValidTransFunctionUsage
     */
    public function testExtractShouldNotRetrieveTransKey($resources)
    {
        $catalogue = new MessageCatalogue(self::TEST_LOCALE);
        $this->extractor->extract($resources, $catalogue);
        $this->assertEmpty($catalogue->all());
    }

    /**
     * @dataProvider resourcesWithATransFunctionUsage
     */
    public function testExtractShouldRetrieveTransKey($resources)
    {
        $catalogue = new MessageCatalogue(self::TEST_LOCALE);
        $this->extractor->extract($resources, $catalogue);
        $this->assertTrue($catalogue->has(self::TEST_KEY_1));
    }

    /**
     * @dataProvider resourcesWithNotValidTransChoiceFunctionUsage
     */
    public function testExtractShouldNotRetrieveTransChoiceKey($resources)
    {
        $catalogue = new MessageCatalogue(self::TEST_LOCALE);
        $this->extractor->extract($resources, $catalogue);
        $this->assertEmpty($catalogue->all());
    }

    /**
     * @dataProvider resourcesWithATransChoiceFunctionUsage
     */
    public function testExtractShouldRetrieveTransChoiceKey($resources)
    {
        $catalogue = new MessageCatalogue(self::TEST_LOCALE);
        $this->extractor->extract($resources, $catalogue);
        $this->assertTrue($catalogue->has(self::TEST_KEY_1));
    }

    public function resourcesWithNotValidTransFunctionUsage()
    {
        return array(
            array(__DIR__.'/../Fixtures/Extractor/NotValidTransFunctionUsage'),
            array(__DIR__.'/../Fixtures/Extractor/NotValidTransFunctionUsage/test.coffee'),
            array(new \SplFileInfo(__DIR__.'/../Fixtures/Extractor/NotValidTransFunctionUsage/test.coffee')),
        );
    }

    public function resourcesWithATransFunctionUsage()
    {
        return array(
            array(__DIR__.'/../Fixtures/Extractor/ATransFunctionUsage'),
            array(__DIR__.'/../Fixtures/Extractor/ATransFunctionUsage/test.coffee'),
            array(new \SplFileInfo(__DIR__.'/../Fixtures/Extractor/ATransFunctionUsage/test.coffee')),
        );
    }

    public function resourcesWithNotValidTransChoiceFunctionUsage()
    {
        return array(
            array(__DIR__.'/../Fixtures/Extractor/NotValidTransChoiceFunctionUsage'),
            array(__DIR__.'/../Fixtures/Extractor/NotValidTransChoiceFunctionUsage/test.coffee'),
            array(new \SplFileInfo(__DIR__.'/../Fixtures/Extractor/NotValidTransChoiceFunctionUsage/test.coffee')),
        );
    }

    public function resourcesWithATransChoiceFunctionUsage()
    {
        return array(
            array(__DIR__.'/../Fixtures/Extractor/ATransChoiceFunctionUsage'),
            array(__DIR__.'/../Fixtures/Extractor/ATransChoiceFunctionUsage/test.coffee'),
            array(new \SplFileInfo(__DIR__.'/../Fixtures/Extractor/ATransChoiceFunctionUsage/test.coffee')),
        );
    }
}

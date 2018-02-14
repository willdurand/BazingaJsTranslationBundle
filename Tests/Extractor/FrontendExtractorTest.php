<?php

namespace Bazinga\JsTranslationBundle\Tests\Extractor;

use Bazinga\Bundle\JsTranslationBundle\Extractor\FrontendExtractor;
use Bazinga\Bundle\JsTranslationBundle\Tests\WebTestCase;
use Symfony\Component\Translation\MessageCatalogue;

final class FrontendExtractorTest extends WebTestCase
{
    const TEST_LOCALE = 'en';
    const TEST_KEY_1 = 'test-key-1';

    /**
     * @var FrontendExtractor
     */
    protected $extractor;

    public function setUp()
    {
      $container = $this->getContainer();
      $this->extractor = $container->get('bazinga.jstranslation.translation_frontend_extractor');
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
        );
    }

    public function resourcesWithATransFunctionUsage()
    {
        return array(
            array(__DIR__.'/../Fixtures/Extractor/ATransFunctionUsage'),
        );
    }

    public function resourcesWithNotValidTransChoiceFunctionUsage()
    {
        return array(
            array(__DIR__.'/../Fixtures/Extractor/NotValidTransChoiceFunctionUsage'),
        );
    }

    public function resourcesWithATransChoiceFunctionUsage()
    {
        return array(
            array(__DIR__.'/../Fixtures/Extractor/ATransChoiceFunctionUsage'),
        );
    }
}

<?php

namespace Bazinga\JsTranslationBundle\Tests\Extractor;

use PHPUnit_Framework_TestCase;
use Symfony\Component\Translation\MessageCatalogue;
use Bazinga\Bundle\JsTranslationBundle\Finder\FinderFactory;
use Bazinga\Bundle\JsTranslationBundle\Filesystem\Filesystem;
use Bazinga\Bundle\JsTranslationBundle\Extractor\JsExtractor;

final class JsExtractorTest extends PHPUnit_Framework_TestCase
{
    const TEST_LOCALE = 'en';
    const TEST_KEY_1 = 'test-key-1';
    const TRANSLATION_PATH_PUBLIC = '/translation-path/public';
    const TRANSLATION_PATH_VIEWS = '/translation-path/views';

    /**
     * @var JsExtractor
     */
    private $extractor;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var FinderFactory
     */
    private $finderFactory;

    /**
     * @var MessageCatalogue
     */
    private $messageCatalogue;

    private $folder;
    private $fileName;
    private $fileContent;

    public function setUp()
    {
        $this->filesystem = $this->prophesize('Bazinga\Bundle\JsTranslationBundle\Filesystem\Filesystem');
        $this->finderFactory = $this->prophesize('Bazinga\Bundle\JsTranslationBundle\Finder\FinderFactory');
        $this->messageCatalogue = new MessageCatalogue(self::TEST_LOCALE);

        $this->extractor = new JsExtractor(
            $this->filesystem->reveal(),
            $this->finderFactory->reveal()
        );
    }

    public function testExtractShouldNotRetrieveTransKey()
    {
        $this->givenASourceFolderWithNotValidTransFunctionUsage();
        $this->thenTheFinderWillFindAJsFile();
        $this->andTheFilesystemWillGrabItsContent();
        $this->whenUsingTheExtractFromTheSut();
        $this->assertTheMessageCatalogueIsEmpty();
    }

    public function testExtractShouldRetrieveTransKey()
    {
        $this->givenASourceFolderWithATransFunctionUsage();
        $this->thenTheFinderWillFindAJsFile();
        $this->andTheFilesystemWillGrabItsContent();
        $this->whenUsingTheExtractFromTheSut();
        $this->assertTheTransKeyIsInMessageCatalogue();
    }

    public function testExtractShouldNotRetrieveTransChoiceKey()
    {
        $this->givenASourceFolderWithNotValidTransChoiceFunctionUsage();
        $this->thenTheFinderWillFindAJsFile();
        $this->andTheFilesystemWillGrabItsContent();
        $this->whenUsingTheExtractFromTheSut();
        $this->assertTheMessageCatalogueIsEmpty();
    }

    public function testExtractShouldRetrieveTransChoiceKey()
    {
        $this->givenASourceFolderWithATransChoiceFunctionUsage();
        $this->thenTheFinderWillFindAJsFile();
        $this->andTheFilesystemWillGrabItsContent();
        $this->whenUsingTheExtractFromTheSut();
        $this->assertTheTransChoiceKeyIsInMessageCatalogue();
    }

    private function givenASourceFolderWithATransFunctionUsage()
    {
        $this->givenASourceFolder();

        $this->fileContent = <<<STRING
        Translator.trans('test-key-1');
        Translator.trans();
        Translator.trans(variable);
STRING;
    }


    private function givenASourceFolderWithNotValidTransFunctionUsage()
    {
        $this->givenASourceFolder();

        $this->fileContent = <<<STRING
        Translator.tras('test-key-1');
        Translator.trns();
        Translator.tans(variable);
STRING;
    }

    private function givenASourceFolderWithNotValidTransChoiceFunctionUsage()
    {
        $this->givenASourceFolder();

        $this->fileContent = <<<STRING
        Translator.transChice('test-key-1', 5);
        Translator.transCohie();
        Translator.transChoce(variable, 5);
STRING;
    }

    private function givenASourceFolderWithATransChoiceFunctionUsage()
    {
        $this->givenASourceFolder();

        $this->fileContent = <<<STRING
        Translator.transChoice('test-key-1', 5);
        Translator.transChoice();
        Translator.transChoice(variable, 5);
STRING;
    }

    private function givenASourceFolder()
    {
        $this->folder = self::TRANSLATION_PATH_VIEWS;
        $this->fileName = 'test.js';
        $this->filesystem
            ->exists(self::TRANSLATION_PATH_PUBLIC)
            ->willReturn(true);
    }

    private function thenTheFinderWillFindAJsFile()
    {
        $finder = $this->prophesize('Symfony\Component\Finder\Finder');

        $finder
            ->files()
            ->shouldBeCalled();

        $finder
            ->name('*.js')
            ->shouldBeCalled();

        $finder
            ->name('*.jsx')
            ->shouldBeCalled();

        $finder
            ->in(self::TRANSLATION_PATH_PUBLIC)
            ->shouldBeCalled()
            ->willReturn(array($this->fileName));

        $this->finderFactory->createNewFinder()->willReturn($finder->reveal());
    }

    private function andTheFilesystemWillGrabItsContent()
    {
        $this->filesystem
            ->getContents($this->fileName)
            ->willReturn($this->fileContent);
    }

    private function whenUsingTheExtractFromTheSut()
    {
        $this->extractor->extract($this->folder, $this->messageCatalogue);
    }

    private function assertTheMessageCatalogueIsEmpty()
    {
        $this->assertEmpty($this->messageCatalogue->all());
    }

    private function assertTheTransKeyIsInMessageCatalogue()
    {
        $this->assertTrue($this->messageCatalogue->has(self::TEST_KEY_1));
    }

    private function assertTheTransChoiceKeyIsInMessageCatalogue()
    {
        $this->assertTrue($this->messageCatalogue->has(self::TEST_KEY_1));
    }
}

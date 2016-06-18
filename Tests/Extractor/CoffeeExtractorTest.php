<?php

namespace Bazinga\JsTranslationBundle\Tests\Extractor;

use PHPUnit_Framework_TestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\MessageCatalogue;
use Bazinga\Bundle\JsTranslationBundle\Finder\FinderFactory;
use Bazinga\Bundle\JsTranslationBundle\Filesystem\Filesystem;
use Bazinga\Bundle\JsTranslationBundle\Extractor\CoffeeExtractor;

final class CoffeeExtractorTest extends PHPUnit_Framework_TestCase
{
    const TEST_LOCALE = 'en';
    const TEST_KEY_1 = 'test-key-1';
    const TRANSLATION_PATH_VIEWS = '/translation-path/views';
    const TRANSLATION_PATH_PUBLIC = '/translation-path/public';

    /**
     * @var JsExtractor
     */
    private $sut;

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
        $this->filesystem = $this->prophesize(Filesystem::class);
        $this->finderFactory = $this->prophesize(FinderFactory::class);
        $this->messageCatalogue = new MessageCatalogue(self::TEST_LOCALE);

        $this->sut = new CoffeeExtractor(
            $this->filesystem->reveal(),
            $this->finderFactory->reveal()
        );
    }

    public function testExtractShouldRetrieveTransKey()
    {
        $this->givenASourceFolderWithATransFunctionUsage();
        $this->thenTheFinderWillFindAJsFile();
        $this->andTheFilesystemWillGrabItsContent();
        $this->whenUsingTheExtractFromTheSut();
        $this->assertTheTransKeyIsInMessageCatalogue();
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
        Translator.trans 'test-key-1'
        Translator.trans
        Translator.trans variable
STRING;
    }

    private function givenASourceFolderWithATransChoiceFunctionUsage()
    {
        $this->givenASourceFolder();

        $this->fileContent = <<<STRING
        Translator.transChoice 'test-key-1', 5
        Translator.transChoice
        Translator.transChoice variable, 5
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
        $finder = $this->prophesize(Finder::class);

        $finder
            ->files()
            ->shouldBeCalled();

        $finder
            ->name('*.coffee')
            ->shouldBeCalled();

        $finder
            ->in(self::TRANSLATION_PATH_PUBLIC)
            ->shouldBeCalled()
            ->willReturn([$this->fileName]);

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
        $this->sut->extract($this->folder, $this->messageCatalogue);
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

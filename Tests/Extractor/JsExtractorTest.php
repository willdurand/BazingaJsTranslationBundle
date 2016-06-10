<?php

namespace Bazinga\JsTranslationBundle\Tests\Extractor;

use PHPUnit_Framework_TestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\MessageCatalogue;
use Bazinga\Bundle\JsTranslationBundle\Filesystem\Filesystem;
use Bazinga\Bundle\JsTranslationBundle\Extractor\JsExtractor;

final class JsExtractorTest extends PHPUnit_Framework_TestCase
{
    const TEST_LOCALE = 'en';
    const TEST_KEY_1 = 'test-key-1';
    const NUMBER_OF_SUPPORTED_EXTENSIONS = 2;

    /**
     * @var JsExtractor
     */
    private $sut;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Finder
     */
    private $finder;

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
        $this->finder = $this->prophesize(Finder::class);
        $this->messageCatalogue = new MessageCatalogue(self::TEST_LOCALE);

        $this->sut = new JsExtractor(
            $this->filesystem->reveal(),
            $this->finder->reveal()
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
        Translator.trans('test-key-1');
        Translator.trans();
        Translator.trans(variable);
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
        $this->folder = '/translation-path/views';
        $this->fileName = 'test.js';
        $this->filesystem
            ->exists('/translation-path/public')
            ->willReturn(true);
    }

    private function thenTheFinderWillFindAJsFile()
    {
        $this->finder
            ->files()
            ->shouldBeCalled();

        $this->finder
            ->name('*.js')
            ->shouldBeCalled();

        $this->finder
            ->name('*.jsx')
            ->shouldBeCalled();

        $this->finder
            ->in('/translation-path/public')
            ->shouldBeCalled()
            ->willReturn([$this->fileName]);
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

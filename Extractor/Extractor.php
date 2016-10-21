<?php

namespace Bazinga\Bundle\JsTranslationBundle\Extractor;

use Symfony\Component\Translation\Extractor\AbstractFileExtractor;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Extractor\ExtractorInterface;
use Bazinga\Bundle\JsTranslationBundle\Finder\FinderFactory;
use Bazinga\Bundle\JsTranslationBundle\Filesystem\Filesystem;

abstract class Extractor extends AbstractFileExtractor implements ExtractorInterface
{
    const FUNCTION_STRING_ARGUMENT_REGEX = '\s?[\'"]([^"\'),]+)[\'"]\s?';
    const REGEX_DELIMITER = '/';

    private $prefix = '';

    private $filesystem;

    private $finderFactory;

    public function __construct(Filesystem $filesystem, FinderFactory $finderFactory) {
        $this->filesystem = $filesystem;
        $this->finderFactory = $finderFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function extract($resource, MessageCatalogue $catalogue)
    {
        $assetsPath = dirname($resource) . '/public';

        if (!$this->filesystem->exists($assetsPath)) {
            return;
        }

        $files = $this->extractFiles($assetsPath);

        foreach ($files as $file) {
            $this->parseMessagesFromContent($this->filesystem->getContents($file), $catalogue);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    private function parseMessagesFromContent($fileContent, MessageCatalogue $catalogue)
    {
        $messages = $this->getMessagesForSequence($fileContent, $this->sequence);

        foreach ($messages as $message) {
            $catalogue->set($message, $this->prefix.$message);
        }
    }

    /**
     * @return array
     */
    abstract protected function getSupportedFileExtensions();

    protected function canBeExtracted($file)
    {
        return $this->isFile($file) && in_array(
            pathinfo($file, PATHINFO_EXTENSION),
            $this->getSupportedFileExtensions()
        );
    }

    protected function isFile($file)
    {
        return $this->filesystem->exists($file);
    }

    protected function extractFromDirectory($directory)
    {
        $finder = $this->finderFactory->createNewFinder();

        $finder->files();

        foreach ($this->getSupportedFileExtensions() as $supportedExtension) {
            $finder->name(sprintf('*.%s', $supportedExtension));
        }

        return $finder->in($directory);
    }

    private function getMessagesForSequence($fileContent, $sequence)
    {
        $argumentsRegex = self::REGEX_DELIMITER
            .$sequence
            .self::FUNCTION_STRING_ARGUMENT_REGEX
            .self::REGEX_DELIMITER;

        preg_match_all($argumentsRegex, $fileContent, $matches);

        if (isset($matches[0]) && !empty($matches[0])) {
            return $matches[1];
        }

        return array();
    }
}

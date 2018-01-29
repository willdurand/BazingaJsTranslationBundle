<?php

namespace Bazinga\Bundle\JsTranslationBundle\Extractor;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\Extractor\AbstractFileExtractor;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Extractor\ExtractorInterface;

abstract class Extractor extends AbstractFileExtractor implements ExtractorInterface
{
    const FUNCTION_STRING_ARGUMENT_REGEX = '\s?[\'"]([^"\'),]+)[\'"]\s?';
    const REGEX_DELIMITER = '/';

    private $prefix = '';

    private $filesystem;

    private $finder;

    public function __construct(Filesystem $filesystem, Finder $finder) {
        $this->filesystem = $filesystem;
        $this->finder = $finder;
    }

    /**
     * {@inheritdoc}
     */
    public function extract($resource, MessageCatalogue $catalogue)
    {
        $files = $this->extractFiles($resource);

        foreach ($files as $file) {
            $this->parseMessagesFromContent(file_get_contents($file), $catalogue);
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
        $this->finder->files();

        foreach ($this->getSupportedFileExtensions() as $supportedExtension) {
            $this->finder->name(sprintf('*.%s', $supportedExtension));
        }

        return $this->finder->in($directory);
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

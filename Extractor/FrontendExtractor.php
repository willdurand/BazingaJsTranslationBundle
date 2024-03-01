<?php

namespace Bazinga\Bundle\JsTranslationBundle\Extractor;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\Extractor\AbstractFileExtractor;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Extractor\ExtractorInterface;

final class FrontendExtractor extends AbstractFileExtractor implements ExtractorInterface
{
    const FUNCTION_STRING_ARGUMENT_REGEX = '\s?[\'"]([^"\'),]+)[\'"]\s?';
    const REGEX_DELIMITER = '/';

    private $prefix = '';

    private $filesystem;

    private $extensions;

    private $sequence;

    public function __construct(Filesystem $filesystem, array $extensions, $sequence) {
        $this->filesystem = $filesystem;
        $this->extensions = $extensions;
        $this->sequence = $sequence;
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
        $messages = $this->getMessagesForSequence($fileContent);

        foreach ($messages as $message) {
            $catalogue->set($message, $this->prefix.$message);
        }
    }

    protected function canBeExtracted($file)
    {
        return $this->isFile($file) && in_array(
            pathinfo($file, PATHINFO_EXTENSION),
            $this->extensions
        );
    }

    protected function isFile($file)
    {
        return $this->filesystem->exists($file);
    }

    protected function extractFromDirectory($directory)
    {
        $finder = new Finder();

        $finder->files();

        foreach ($this->extensions as $supportedExtension) {
            $finder->name(sprintf('*.%s', $supportedExtension));
        }

        return $finder->in($directory);
    }

    private function getMessagesForSequence($fileContent)
    {
        $argumentsRegex = self::REGEX_DELIMITER
            .$this->sequence
            .self::FUNCTION_STRING_ARGUMENT_REGEX
            .self::REGEX_DELIMITER;

        preg_match_all($argumentsRegex, $fileContent, $matches);

        if (isset($matches[0]) && !empty($matches[0])) {
            return $matches[1];
        }

        return array();
    }
}

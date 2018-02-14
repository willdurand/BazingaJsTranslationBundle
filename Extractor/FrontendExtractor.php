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

    private $configuration;

    public function __construct(Filesystem $filesystem, array $configuration = []) {
        $this->filesystem = $filesystem;
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function extract($resource, MessageCatalogue $catalogue)
    {
        $files = $this->extractFiles($resource);

        foreach ($files as $file) {
            foreach ($this->configuration as $configuration)
            {
              $pathInfo = pathinfo($file);
              if (in_array($pathInfo['extension'], $configuration['extensions'], true))
              {
                $this->parseMessagesFromContent(file_get_contents($file), $catalogue, $configuration['sequence']);
              }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    private function parseMessagesFromContent($fileContent, MessageCatalogue $catalogue, $sequence)
    {
        $messages = $this->getMessagesForSequence($fileContent, $sequence);

        foreach ($messages as $message) {
            $catalogue->set($message, $this->prefix.$message);
        }
    }

    /**
     * @return array
     */
    protected function getSupportedFileExtensions()
    {
      return call_user_func_array('array_merge', array_column($this->configuration, 'extensions'));
    }

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
        $finder = new Finder();

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

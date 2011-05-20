<?php

namespace Bazinga\ExposeTranslationBundle\Service;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Finder\Finder;

/**
 * TranslationFinder class.
 *
 * @package Service
 * @author William DURAND <william.durand1@gmail.com>
 */
class TranslationFinder
{
    /**
     * @var \Symfony\Component\HttpKernel\KernelInterface
     */
    protected $kernel;

    /**
     * Default constructor.
     * @param \Symfony\Component\HttpKernel\KernelInterface $kernel             The kernel.
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Returns an array of translation files for a given domain and a given locale.
     *
     * @param string $domainName    A domain translation name.
     * @param string $locale        A locale.
     * @return array                An array of translation files.
     */
    public function getResources($domainName, $locale)
    {
        $finder = new Finder();

        $locations = array();
        foreach ($this->kernel->getBundles() as $bundle) {
            if (is_dir($bundle->getPath() . '/Resources/translations')) {
                $locations[] = $bundle->getPath() . '/Resources/translations';
            }
        }

        if (is_dir($this->kernel->getRootDir() . '/Resources/translations')) {
            $locations[] = $this->kernel->getRootDir() . '/Resources/translations';
        }

        return $finder->files()->name($domainName . '.' . $locale . '.*')->followLinks()->in($locations);
    }
}

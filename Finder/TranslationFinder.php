<?php

namespace Bazinga\ExposeTranslationBundle\Finder;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Finder\Finder;

/**
 * @author William DURAND <william.durand1@gmail.com>
 * @author Markus Poerschke <markus@eluceo.de>
 */
class TranslationFinder
{
    /**
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * @param KernelInterface $kernel The kernel.
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Returns an array of translation files for a given domain and a given locale.
     *
     * @param  string $domainName A domain translation name.
     * @param  string $locale     A locale.
     * @return array  An array of translation files.
     */
    public function getResources($domainName, $locale)
    {
        $finder = new Finder();

        return $finder
            ->files()
            ->name($domainName . '.' . $locale . '.*')
            ->followLinks()
            ->in($this->getLocations());
    }

    /**
     * Returns an array of all translation files.
     *
     * @return array An array of translation files.
     */
    public function getAllResources()
    {
        $finder = new Finder();
        $finder
            ->files()
            ->in($this->getLocations())
            ->followLinks();

        return $finder;
    }

    /**
     * Returns an array of (unique) locales and their fallback.
     *
     * @param  array $locales An array of locales.
     * @return array An array of unique locales.
     */
    public function createLocalesArray(array $locales)
    {
        $returnLocales = array();

        foreach ($locales as $locale) {
            if (empty($locale)) {
                continue;
            }

            if (2 === strpos($locale, '_') && 5 === strlen($locale)) {
                $returnLocales[] = substr($locale, 0, 2);
            }

            $returnLocales[] = $locale;
        }

        return array_values(array_unique($returnLocales));
    }

    /**
     * Gets translation files location.
     *
     * @return array
     */
    private function getLocations()
    {
        $locations = array();

        if (class_exists('Symfony\Component\Validator\Validator')) {
            $r = new \ReflectionClass('Symfony\Component\Validator\Validator');

            $locations[] = dirname($r->getFilename()).'/Resources/translations';
        }

        if (class_exists('Symfony\Component\Form\Form')) {
            $r = new \ReflectionClass('Symfony\Component\Form\Form');

            $locations[] = dirname($r->getFilename()).'/Resources/translations';
        }

        if (class_exists('Symfony\Component\Security\Core\Exception\AuthenticationException')) {
            $r = new \ReflectionClass('Symfony\Component\Security\Core\Exception\AuthenticationException');

            if (file_exists($dir = dirname($r->getFilename()).'/../../Resources/translations')) {
                $locations[] = $dir;
            } else {
                // Symfony 2.4 and above
                $locations[] = dirname($r->getFilename()).'/../Resources/translations';
            }
        }

        $overridePath = $this->kernel->getRootDir() . '/Resources/%s/translations';
        foreach ($this->kernel->getBundles() as $bundle => $class) {
            $reflection = new \ReflectionClass($class);
            if (is_dir($dir = dirname($reflection->getFilename()).'/Resources/translations')) {
                $locations[] = $dir;
            }
            if (is_dir($dir = sprintf($overridePath, $bundle))) {
                $locations[] = $dir;
            }
        }

        if (is_dir($dir = $this->kernel->getRootDir() . '/Resources/translations')) {
            $locations[] = $dir;
        }

        return $locations;
    }
}

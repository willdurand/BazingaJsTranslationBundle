<?php

namespace Bazinga\Bundle\JsTranslationBundle\Dumper;

use Bazinga\Bundle\JsTranslationBundle\Util;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Translation\TranslatorBagInterface;
use Twig\Environment;

/**
 * @author Adrien Russo <adrien.russo.qc@gmail.com>
 * @author Hugo Monteiro <hugo.monteiro@gmail.com>
 *
 * @internal
 */
class TranslationDumper
{
    const DEFAULT_TRANSLATION_PATTERN = '/translations/{domain}.{_format}';

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var TranslatorBagInterface
     */
    private $translatorBag;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var array List of locales translations to dump
     */
    private $activeLocales;

    /**
     * @var array List of domains translations to dump
     */
    private $activeDomains;

    /**
     * @var string
     */
    private $localeFallback;

    /**
     * @var string
     */
    private $defaultDomain;

    /**
     * @param Environment                   $twig           The twig environment.
     * @param TranslatorBagInterface        $translatorBag  The translator bag.
     * @param FileSystem                    $filesystem     The file system.
     * @param string                        $localeFallback
     * @param string                        $defaultDomain
     */
    public function __construct(
        Environment $twig,
        TranslatorBagInterface  $translatorBag,
        Filesystem $filesystem,
        $localeFallback = '',
        $defaultDomain  = '',
        array $activeLocales = array(),
        array $activeDomains = array()
    ) {
        $this->twig           = $twig;
        $this->translatorBag     = $translatorBag;
        $this->filesystem     = $filesystem;
        $this->localeFallback = $localeFallback;
        $this->defaultDomain  = $defaultDomain;
        $this->activeLocales  = $activeLocales;
        $this->activeDomains  = $activeDomains;
    }

    /**
     * Get array of active locales
     */
    public function getActiveLocales()
    {
        return $this->activeLocales;
    }

    /**
     * Get array of active locales
     */
    public function getActiveDomains()
    {
        return $this->activeDomains;
    }

    /**
     * Dump all translation files.
     *
     * @param string $target Target directory.
     * @param string $pattern route path
     * @param string[] $formats Formats to generate.
     * @param \stdClass $merge Merge options.
     */
    public function dump(
        $target = 'web/js',
        $pattern = self::DEFAULT_TRANSLATION_PATTERN,
        array $formats = array(),
        \stdClass $merge = null
    ) {
        $availableFormats  = array('js', 'json');

        $parts = array_filter(explode('/', $pattern));
        $this->filesystem->remove($target. '/' . current($parts));

        foreach ($formats as $format) {
            if (!in_array($format, $availableFormats)) {
                throw new \RuntimeException('The ' . $format . ' format is not available. Use only: ' . implode(', ', $availableFormats) . '.');
            }
        }

        if (empty($formats)) {
            $formats = $availableFormats;
        }

        $this->dumpConfig($pattern, $formats, $target);

        if ($merge && $merge->domains) {
            $this->dumpTranslationsPerLocale($pattern, $formats, $target);
        } else {
            $this->dumpTranslationsPerDomain($pattern, $formats, $target);
        }
    }

    private function dumpConfig($pattern, array $formats, $target)
    {
        foreach ($formats as $format) {
            $file = sprintf('%s/%s',
                $target,
                strtr($pattern, array(
                    '{domain}'  => 'config',
                    '{_format}' => $format
                ))
            );

            $this->filesystem->mkdir(dirname($file));

            if (file_exists($file)) {
                $this->filesystem->remove($file);
            }

            file_put_contents(
                $file,
                $this->twig->render('@BazingaJsTranslation/config.' . $format . '.twig', array(
                    'fallback'      => $this->localeFallback,
                    'defaultDomain' => $this->defaultDomain,
                ))
            );
        }
    }

    private function dumpTranslationsPerDomain($pattern, array $formats, $target)
    {
        foreach ($this->getTranslations() as $locale => $domains) {
            foreach ($domains as $domain => $translations) {

                $cleanedDomain = Util::cleanDomain($domain);
                if ($domain !== $cleanedDomain && !in_array($cleanedDomain, $domains, true)) {
                    // e.g.: skip "messages+intl-icu" if "messages" exists. They will get merged after.
                    continue;
                }

                $renderContext = array(
                    'translations'   => array($locale => $this->filterTranslationsByDomain($domains, $cleanedDomain)),
                    'include_config' => false,
                );

                foreach ($formats as $format) {
                    $content = $this->twig->render('@BazingaJsTranslation/getTranslations.' . $format . '.twig', $renderContext);

                    $file = sprintf('%s/%s',
                        $target,
                        strtr($pattern, array(
                            '{domain}'  => sprintf('%s/%s', $domain, $locale),
                            '{_format}' => $format
                        ))
                    );

                    $this->filesystem->mkdir(dirname($file));

                    if (file_exists($file)) {
                        $this->filesystem->remove($file);
                    }

                    file_put_contents($file, $content);
                }
            }
        }
    }

    private function dumpTranslationsPerLocale($pattern, array $formats, $target)
    {
        foreach ($this->getTranslations() as $locale => $domains) {
            foreach ($formats as $format) {
                $content = $this->twig->render(
                    '@BazingaJsTranslation/getTranslations.' . $format . '.twig',
                    array(
                        'translations' => array($locale => $domains),
                        'include_config' => false,
                    )
                );

                $file = sprintf(
                    '%s/%s',
                    $target,
                    strtr(
                        $pattern,
                        array(
                            '{domain}' => $locale,
                            '{_format}' => $format
                        )
                    )
                );

                if (file_exists($file)) {
                    $this->filesystem->remove($file);
                }

                file_put_contents($file, $content);
            }
        }
    }

    /**
     * @return array
     */
    private function getTranslations()
    {
        $translations = array();
        $activeLocales = $this->activeLocales;
        $activeDomains = $this->activeDomains;

        sort($activeLocales);
        sort($activeDomains);

        foreach ($activeLocales as $locale) {
            $translations[$locale] = array();

            foreach ($activeDomains as $domain) {
                $translations[$locale] = array_merge(
                    $translations[$locale],
                    Util::getMessagesFromTranslatorBag($this->translatorBag, $locale, $domain)
                );
            }
        }

        return $translations;
    }

    /**
     * Filter an array of translations by $cleanedDomain.
     *
     * For example, if $cleanedDomain equals "messages", it will returns translations
     * from "messages" and "messages+intl-icu" (if exists) catalogues.
     */
    private function filterTranslationsByDomain(array $domains, string $cleanedDomain): array
    {
        $ret = [];

        foreach ($domains as $domain => $translations) {
            if (Util::cleanDomain($domain) === $cleanedDomain) {
                $ret[$domain] = $translations;
            }
        }

        return $ret;
    }
}

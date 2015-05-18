<?php

namespace Bazinga\Bundle\JsTranslationBundle\Controller;

use Bazinga\Bundle\JsTranslationBundle\Finder\TranslationFinder;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author William DURAND <william.durand1@gmail.com>
 */
class Controller
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var EngineInterface
     */
    private $engine;

    /**
     * @var TranslationFinder
     */
    private $translationFinder;

    /**
     * @var array
     */
    private $loaders = array();

    /**
     * @var string
     */
    private $cacheDir;

    /**
     * @var boolean
     */
    private $debug;

    /**
     * @var string
     */
    private $localeFallback;

    /**
     * @var string
     */
    private $defaultDomain;

    /**
     * @var int
     */
    private $httpCacheTime;

    /**
     * @param TranslatorInterface $translator        The translator.
     * @param EngineInterface     $engine            The engine.
     * @param TranslationFinder   $translationFinder The translation finder.
     * @param string              $cacheDir
     * @param boolean             $debug
     * @param string              $localeFallback
     * @param string              $defaultDomain
     * @param int                 $httpCacheTime
     */
    public function __construct(
        TranslatorInterface $translator,
        EngineInterface $engine,
        TranslationFinder $translationFinder,
        $cacheDir,
        $debug          = false,
        $localeFallback = '',
        $defaultDomain  = '',
        $httpCacheTime  = 86400
    ) {
        $this->translator        = $translator;
        $this->engine            = $engine;
        $this->translationFinder = $translationFinder;
        $this->cacheDir          = $cacheDir;
        $this->debug             = $debug;
        $this->localeFallback    = $localeFallback;
        $this->defaultDomain     = $defaultDomain;
        $this->httpCacheTime     = $httpCacheTime;
    }

    /**
     * Add a translation loader if it does not exist.
     *
     * @param string          $id     The loader id.
     * @param LoaderInterface $loader A translation loader.
     */
    public function addLoader($id, $loader)
    {
        if (!array_key_exists($id, $this->loaders)) {
            $this->loaders[$id] = $loader;
        }
    }

    public function getTranslationsAction(Request $request, $domain, $_format)
    {
        $requestedLocales = $this->getLocales($request);

        if (empty($requestedLocales)) {
            throw new NotFoundHttpException();
        }

        $cache = new ConfigCache(sprintf('%s/%s.%s.%s',
            $this->cacheDir,
            $domain,
            implode('-', $requestedLocales),
            $_format
        ), $this->debug);

        if (!$cache->isFresh()) {
            $derivedLocaleMap = $this->createDerivedLocaleMap($requestedLocales);

            $derivedLocales = array();

            foreach ($derivedLocaleMap as $locale) {
                $derivedLocales[$locale] = $locale;
            }

            $effectiveLocales = array_merge($derivedLocales, $requestedLocales);

            //Load translations:

            $resources    = array();
            $translations = array();

            foreach ($effectiveLocales as $effectiveLocale) {
                $translations[$effectiveLocale] = array();

                $files = $this->translationFinder->get($domain, $effectiveLocale);

                if (1 > count($files)) {
                    continue;
                }

                $translations[$effectiveLocale][$domain] = array();

                foreach ($files as $file) {
                    /*@var $file \Symfony\Component\Finder\SplFileInfo*/

                    $extension = $file->getExtension();

                    if (!isset($this->loaders[$extension])) {
                        continue;
                    }

                    $resources[] = new FileResource($file->getPath());

                    $catalogue   = $this->loaders[$extension]->load($file, $effectiveLocale, $domain);

                    $translations[$effectiveLocale][$domain] = array_replace_recursive(
                        $translations[$effectiveLocale][$domain],
                        $catalogue->all($domain)
                    );
                }
            }

            //Compile a final list of translations - apparently - containing only translations for the requested
            //locales:

            $requestedTranslations = array();

            foreach ($requestedLocales as $requestedLocale) {
                if (array_key_exists($requestedLocale, $derivedLocaleMap)) {
                    //Get the translations for the 'root' locale from which the requested 'regional' locale derives.
                    $rootLocaleTranslations = $translations[$derivedLocaleMap[$requestedLocale]];

                    //Merge the regional translations into the translations for the 'root' locale.
                    $requestedTranslations[$requestedLocale] = array_merge_recursive(
                        $rootLocaleTranslations,
                        $translations[$requestedLocale]
                    );
                } else {
                    $requestedTranslations[$requestedLocale] = $translations[$requestedLocale];
                }
            }

            //Render, and then cache, content for the response:

            $content = $this->engine->render("BazingaJsTranslationBundle::getTranslations.{$_format}.twig", array(
                'fallback'       => $this->localeFallback,
                'defaultDomain'  => $this->defaultDomain,
                'translations'   => $requestedTranslations,
                'include_config' => true,
            ));

            try {
                $cache->write($content, $resources);
            } catch (IOException $e) {
                throw new NotFoundHttpException();
            }
        }

        $response = $this->createResponse($request, file_get_contents((string) $cache), $_format);

        return $response;
    }

    /**
     * @param Request $request
     * @return array
     */
    private function getLocales(Request $request)
    {
        $queryLocales = $request->query->get('locales');

        $locales = array();

        if (null !== $queryLocales) {
            $locales = explode(',', $queryLocales);
        } else {
            $locales = array($request->getLocale());
        }

        $locales = array_filter($locales, function ($locale) {
            return 1 === preg_match('/^[a-z]{2}([-_]{1}[a-zA-Z]{2})?$/', $locale);
        });

        $locales = array_unique(array_map(function ($locale) {
            return trim($locale);
        }, $locales));

        //Key on locale code - like `["en_GB" => "en_GB"]`.
        return array_combine($locales, $locales);
    }

    /**
     * Returns an array that maps two-part locale codes (e.g. "en_GB") in the specified array to the code for the 'root'
     * locale from which each of them derives (e.g. "en").
     *
     * @param array $locales
     * @return array
     */
    private function createDerivedLocaleMap(array $locales)
    {
        $map = array();

        foreach ($locales as $locale) {
            if (strpos($locale, '_') === false) {
                continue;
            }

            $parts = explode('_', $locale);
            $languageCode = reset($parts);
            $map[$locale] = $languageCode;
        }

        return $map;
    }

    /**
     * Factory method that creates a response for translations.
     *
     * @param Request $request
     * @param string $content
     * @param string $_format
     * @return Response
     */
    private function createResponse(Request $request, $content, $_format)
    {
        $expirationTime = new \DateTime("+{$this->httpCacheTime} seconds");

        $response = new Response(
            $content,
            200,
            array('Content-Type' => $request->getMimeType($_format))
        );

        $response->prepare($request);
        $response->setPublic();
        $response->setETag(md5($response->getContent()));
        $response->isNotModified($request);
        $response->setExpires($expirationTime);

        return $response;
    }
}

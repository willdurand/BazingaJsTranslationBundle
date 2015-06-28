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
use Bazinga\Bundle\JsTranslationBundle\LocaleCode;

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
        $localeCodes = $this->createLocaleCodesFromRequest($request);

        $cache = new ConfigCache($this->createCacheFilename($domain, $localeCodes, $_format), $this->debug);

        if (!$cache->isFresh()) {
            //Load translations:

            $resources    = array();
            $translations = array();

            foreach ($localeCodes as $localeCode) {
                $symfonyLocaleCodeStrings = array($localeCode->getString() => $localeCode->getSymfonyString());

                //If the locale code has a country code (i.e. a second part, as in "en-GB", for example) then also fetch
                //the 'base' translations.
                if ($localeCode->hasCountryCode()) {
                    $symfonyLocaleCodeStrings[$localeCode->getLanguageCode()] = $localeCode->getLanguageCode();
                }

                foreach ($symfonyLocaleCodeStrings as $localeCodeString => $symfonyLocaleCodeString) {
                    $translations[$localeCodeString] = array();

                    $files = $this->translationFinder->get($domain, $symfonyLocaleCodeString);

                    if (1 > count($files)) {
                        continue;
                    }

                    $translations[$localeCodeString][$domain] = array();

                    foreach ($files as $file) {
                        /*@var $file \Symfony\Component\Finder\SplFileInfo*/

                        $extension = $file->getExtension();

                        if (!isset($this->loaders[$extension])) {
                            continue;
                        }

                        $resources[] = new FileResource($file->getPath());

                        $catalogue   = $this->loaders[$extension]->load($file, $symfonyLocaleCodeString, $domain);

                        $translations[$localeCodeString][$domain] = array_replace_recursive(
                            $translations[$localeCodeString][$domain],
                            $catalogue->all($domain)
                        );
                    }
                }
            }

            //Compile a final list of translations containing - apparently - only translations for the requested
            //locales:

            $requestedTranslations = array();

            foreach ($localeCodes as $localeCode) {
                $localeCodeString = $localeCode->getString();

                $baseTranslations = array();

                if ($localeCode->hasCountryCode()) {
                    $baseTranslations = $translations[$localeCode->getLanguageCode()];
                }

                $requestedTranslations[$localeCodeString] = array_replace_recursive(
                    $baseTranslations,
                    $translations[$localeCodeString]
                );
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
     * @return LocaleCode[]
     */
    private function createLocaleCodesFromRequest(Request $request)
    {
        $localeCodeStringsCsv = $request->query->get('locales', $request->getLocale());
        $localeCodeStrings = array_unique(explode(',', $localeCodeStringsCsv));

        $localeCodes = array();

        foreach ($localeCodeStrings as $localeCodeString) {
            try {
                $localeCodes[] = new LocaleCode($localeCodeString);
            } catch (\Exception $e) {
                throw new NotFoundHttpException();
            }
        }

        return $localeCodes;
    }

    /**
     * @param string $domain
     * @param LocaleCode[] $localeCodes
     * @param string $format
     * @return string
     */
    private function createCacheFilename($domain, array $localeCodes, $format)
    {
        $localeCodeStrings = array();

        foreach ($localeCodes as $localeCode) {
            $localeCodeStrings[] = $localeCode->getString();
        }

        $localeCodesId = implode('-', $localeCodeStrings);

        return sprintf('%s/%s.%s.%s', $this->cacheDir, $domain, $localeCodesId, $format);
    }

    /**
     * Factory method that creates a response for translations.
     *
     * @param Request $request
     * @param string $content
     * @param string $format
     * @return Response
     */
    private function createResponse(Request $request, $content, $format)
    {
        $expirationTime = new \DateTime("+{$this->httpCacheTime} seconds");

        $response = new Response(
            $content,
            200,
            array('Content-Type' => $request->getMimeType($format))
        );

        $response->prepare($request);
        $response->setPublic();
        $response->setETag(md5($response->getContent()));
        $response->isNotModified($request);
        $response->setExpires($expirationTime);

        return $response;
    }
}

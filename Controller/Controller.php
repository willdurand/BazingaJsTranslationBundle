<?php

namespace Bazinga\Bundle\JsTranslationBundle\Controller;

use Bazinga\Bundle\JsTranslationBundle\Util;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Translation\TranslatorBagInterface;
use Twig\Environment;

/**
 * @author William DURAND <william.durand1@gmail.com>
 *
 * @final
 */
class Controller
{
    /**
     * @var TranslatorBagInterface
     */
    private $translatorBag;

    /**
     * @var Environment
     */
    private $twig;

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
     * @param TranslatorBagInterface        $translatorBag The translator bag.
     * @param Environment                   $twig The twig environment.
     * @param string                        $cacheDir
     * @param boolean                       $debug
     * @param string                        $localeFallback
     * @param string                        $defaultDomain
     * @param int                           $httpCacheTime
     */
    public function __construct(
        TranslatorBagInterface $translatorBag,
        Environment $twig,
        $cacheDir,
        $debug          = false,
        $localeFallback = '',
        $defaultDomain  = '',
        $httpCacheTime  = 86400
    ) {
        $this->translatorBag     = $translatorBag;
        $this->twig              = $twig;
        $this->cacheDir          = $cacheDir;
        $this->debug             = $debug;
        $this->localeFallback    = $localeFallback;
        $this->defaultDomain     = $defaultDomain;
        $this->httpCacheTime     = $httpCacheTime;
    }

    public function getTranslationsAction(Request $request, $domain, $_format)
    {
        $locales = $this->getLocales($request);

        if (0 === count($locales)) {
            throw new NotFoundHttpException();
        }

        $cache = new ConfigCache(sprintf('%s/%s.%s.%s',
            $this->cacheDir,
            $domain,
            implode('-', $locales),
            $_format
        ), $this->debug);

        if (!$cache->isFresh()) {
            $resources    = array();
            $translations = array();

            foreach ($locales as $locale) {
                $translations[$locale] = array();

                $translations[$locale] = array_merge(
                    $translations[$locale],
                    Util::getMessagesFromTranslatorBag($this->translatorBag, $locale, $domain)
                );
            }

            $content = $this->twig->render('@BazingaJsTranslation/getTranslations.' . $_format . '.twig', array(
                'fallback'       => $this->localeFallback,
                'defaultDomain'  => $this->defaultDomain,
                'translations'   => $translations,
                'include_config' => true,
            ));

            try {
                $cache->write($content, $resources);
            } catch (IOException $e) {
                throw new NotFoundHttpException();
            }
        }

        if (method_exists($cache, 'getPath')) {
            $cachePath = $cache->getPath();
        } else {
            $cachePath = (string) $cache;
        }

        $expirationTime = new \DateTime();
        $expirationTime->modify('+' . $this->httpCacheTime . ' seconds');
        $response = new Response(
            file_get_contents($cachePath),
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

    private function getLocales(Request $request)
    {
        if (null !== $locales = $request->query->get('locales')) {
            $locales = explode(',', $locales);
        } else {
            $locales = array($request->getLocale());
        }

        $locales = array_filter($locales, function ($locale) {
            return 1 === preg_match('/^[a-z]{2,3}([-_]{1}[a-zA-Z]{2})?$/', $locale);
        });

        $locales = array_unique(array_map(function ($locale) {
            return trim($locale);
        }, $locales));

        return $locales;
    }
}

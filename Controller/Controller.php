<?php

namespace Bazinga\ExposeTranslationBundle\Controller;

use Bazinga\ExposeTranslationBundle\Finder\TranslationFinder;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author William DURAND <william.durand1@gmail.com>
 */
class Controller
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;
    /**
     * @var EngineInterface
     */
    protected $engine;
    /**
     * @var TranslationFinder
     */
    protected $translationFinder;
    /**
     * @var array
     */
    protected $defaultDomains;

    /**
     * @var string
     */
    protected $cacheDir;

    /**
     * @var bool
     */
    protected $debug;

    /**
     * @var string
     */
    protected $localeFallback;

    /**
     * @var array
     */
    protected $loaders;

    /**
     * Default constructor.
     *
     * @param TranslatorInterface $translator        The translator.
     * @param EngineInterface     $engine            The engine.
     * @param TranslationFinder   $translationFinder The translation finder.
     * @param string              $cacheDir
     * @param boolean             $debug
     * @param string              $localeFallback
     * @param array               $defaultDomains    An array of default domain names.
     */
    public function __construct(TranslatorInterface $translator, EngineInterface $engine,
                                TranslationFinder $translationFinder, $cacheDir, $debug = false, $localeFallback = "", array $defaultDomains = array(), $loaders = array())
    {
        $this->translator        = $translator;
        $this->engine            = $engine;
        $this->translationFinder = $translationFinder;
        $this->defaultDomains    = $defaultDomains;
        $this->cacheDir          = $cacheDir;
        $this->debug             = $debug;
        $this->localeFallback    = $localeFallback;
        $this->loaders           = $loaders;
    }

    /**
     * exposeTranslationAction action.
     */
    public function exposeTranslationAction(Request $request, $domain_name, $_locale, $_format)
    {
        $cache = new ConfigCache($this->cacheDir.'/'.$domain_name.'.'.$_locale.".".$_format, $this->debug);

        if (!$cache->isFresh()) {
            $locales = $this->translationFinder->createLocalesArray(array($this->localeFallback, $_locale));
            $files = array();

            foreach ($locales as $locale) {
                foreach ($this->translationFinder->getResources($domain_name, $locale) as $file) {
                    $files[] = $file;
                }
            }

            $resources = array();

            $catalogues = array();
            foreach ($files as $file) {
                $extension = pathinfo($file->getFilename(), \PATHINFO_EXTENSION);

                if (isset($this->loaders[$extension])) {
                    $resources[] = new FileResource($file->getPath());
                    $catalogues[] = $this->container->get($this->loaders[$extension])->load($file, $_locale, $domain_name);
                }
            }

            $messages = array();
            foreach ($catalogues as $catalogue) {
                $messages = array_merge_recursive($messages, $catalogue->all());
            }

            foreach ($messages as &$domain) {
                $domain = array_map(function($m){ return is_array($m) ? end($m) : $m; }, $domain);
            }

            $content = $this->engine->render('BazingaExposeTranslationBundle::exposeTranslation.' . $_format . '.twig', array(
                'messages'        => $messages,
                'locale'          => $_locale,
                'defaultDomains'  => $this->defaultDomains,
            ));

            $cache->write($content, $resources);
        }

        $content = file_get_contents((string) $cache);

        return new Response($content, 200, array('Content-Type' => $request->getMimeType($_format)));
    }
}

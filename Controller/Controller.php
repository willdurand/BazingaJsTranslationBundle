<?php

namespace Bazinga\ExposeTranslationBundle\Controller;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Bazinga\ExposeTranslationBundle\Service\TranslationFinder;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Resource\FileResource;

/**
 * Controller class.
 *
 * @package ExposeTranslationBundle
 * @subpackage Controller
 * @author William DURAND <william.durand1@gmail.com>
 */
class Controller
{
    /**
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    protected $translator;
    /**
     * @var \Symfony\Component\Templating\EngineInterface
     */
    protected $engine;
    /**
     * @var \Bazinga\ExposeTranslationBundle\Service\TranslationFinder
     */
    protected $translationFinder;
    /**
     * @var array
     */
    protected $loaders;
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
     * Default constructor.
     *
     * @param \Symfony\Component\Translation\TranslatorInterface $translator                The translator.
     * @param \Symfony\Component\Templating\EngineInterface $engine                         The engine.
     * @param \Bazinga\ExposeTranslationBundle\Service\TranslationFinder $translationFinder The translation finder.
     * @param string $cacheDir
     * @param boolean $debug
     * @param string $localeFallback
     * @param array $defaultDomains     An array of default domain names.
     */
    public function __construct(TranslatorInterface $translator, EngineInterface $engine,
                                TranslationFinder $translationFinder, $cacheDir, $debug = false, $localeFallback = "", array $defaultDomains = array())
    {
        $this->translator        = $translator;
        $this->engine            = $engine;
        $this->translationFinder = $translationFinder;
        $this->defaultDomains    = $defaultDomains;
        $this->loaders           = array();
        $this->cacheDir          = $cacheDir;
        $this->debug             = $debug;
        $this->localeFallback    = $localeFallback;
    }

    /**
     * Add a translation loader if it does not exist.
     *
     * @param string $id    The loader id.
     * @param \Symfony\Component\Translation\Loader\LoaderInterface $loader     A translation loader.
     */
    public function addLoader($id, $loader)
    {
        if (!array_key_exists($id, $this->loaders)) {
            $this->loaders[$id] = $loader;
        }
    }

    /**
     * exposeTranslationAction action.
     */
    public function exposeTranslationAction(Request $request, $domain_name, $_locale, $_format)
    {
        $cache = new ConfigCache($this->cacheDir.'/'.$domain_name.'.'.$_locale.".".$_format, $this->debug);

        if (!$cache->isFresh()) {
            $files = $this->translationFinder->getResources($domain_name, $_locale);
            $files = iterator_to_array($files);

            if ($this->localeFallback && $_locale !== $this->localeFallback) {
                $fallbackFiles = $this->translationFinder->getResources($domain_name, $this->localeFallback);
                $fallbackFiles = iterator_to_array($fallbackFiles);
                $files = array_merge($fallbackFiles, $files);
            }

            $resources = array();

            $catalogues = array();
            foreach ($files as $file) {
                $extension = pathinfo($file->getFilename(), \PATHINFO_EXTENSION);

                if (isset($this->loaders[$extension])) {
                    $resources[] = new FileResource($file->getPath());
                    $catalogues[] = $this->loaders[$extension]->load($file, $_locale, $domain_name);
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

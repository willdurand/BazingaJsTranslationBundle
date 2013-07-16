<?php

namespace Bazinga\ExposeTranslationBundle\Controller;

use Symfony\Component\HttpFoundation\StreamedResponse;

use Bazinga\ExposeTranslationBundle\Finder\TranslationFinder;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Resource\FileResource;

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
     * @param TranslatorInterface $translator        The translator.
     * @param EngineInterface     $engine            The engine.
     * @param TranslationFinder   $translationFinder The translation finder.
     * @param string              $cacheDir
     * @param boolean             $debug
     * @param string              $localeFallback
     * @param array               $defaultDomains    An array of default domain names.
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
     * @param string          $id     The loader id.
     * @param LoaderInterface $loader A translation loader.
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
        $cache = $this->collectMessages($_locale, $_format, array($domain_name));

        $response = new StreamedResponse(function()use($cache){
    		readfile((string) $cache);
    	});
    	$response->prepare($request);
    	$response->setPublic();
    	$response->setLastModified(new \DateTime("@".filemtime((string)$cache)));
    	$response->isNotModified($request);

    	return $response;
    }
    /**
     * exposeTranslationAction action.
     */
    public function exposeTranslationsAction(Request $request, $_locale, $_format)
    {

    	$domains = $request->get("domains");

    	$cache = $this->collectMessages($_locale, $_format, $domains);

    	$response = new StreamedResponse(function()use($cache){
    		readfile((string) $cache);
    	});
    	$response->prepare($request);
    	$response->setPublic();
    	$response->setLastModified(new \DateTime("@".filemtime((string)$cache)));
    	$response->isNotModified($request);

    	return $response;
    }
    /**
     * @param string $locale
     * @param string $format
     * @param array $domains
     * @return \Symfony\Component\Config\ConfigCache
     */
    protected function collectMessages($locale, $format, array $domains)
    {
    	$cache = new ConfigCache($this->cacheDir.'/'.implode(".", $domains).'.'.$locale.".".$format, $this->debug);

		if (!$cache->isFresh()) {

    		$locales = $this->translationFinder->createLocalesArray(array($this->localeFallback, $locale));

    		$files = array();
    		$messages = array();
    		$catalogues = array();
    		$resources = array();

    		foreach($domains as $domain_name){
	    		foreach ($locales as $locale) {
	    			foreach ($this->translationFinder->getResources($domain_name, $locale) as $file) {
	    				$files[] = $file;
	    			}
	    		}

	    		foreach ($files as $file) {
	    			$extension = $file->getExtension();
	    			if (isset($this->loaders[$extension])) {
	    				$resources[] = new FileResource($file->getPath());
	    				$catalogues[] = $this->loaders[$extension]->load($file, $locale, $domain_name);
	    			}
	    		}
    		}

    		foreach ($catalogues as $catalogue) {
    			$messages = array_merge_recursive($messages, $catalogue->all());
    		}
    		foreach ($messages as &$domain) {
    			$domain = array_map(function($m){ return is_array($m) ? end($m) : $m; }, $domain);
    		}
    		$content = $this->engine->render('BazingaExposeTranslationBundle::exposeTranslation.' . $format . '.twig', array(
    				'messages'        => $messages,
    				'locale'          => $locale,
    				'defaultDomains'  => $this->defaultDomains,
    		));

    		$cache->write($content, $resources);
    	}
    	return $cache;
    }
}

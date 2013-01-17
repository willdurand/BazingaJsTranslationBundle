<?php

namespace Bazinga\ExposeTranslationBundle\Dumper;

use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouterInterface;

use Bazinga\ExposeTranslationBundle\Finder\TranslationFinder;

/**
 * @author Adrien Russo <adrien.russo.qc@gmail.com>
 */
class TranslationDumper
{
    /**
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * @var EngineInterface
     */
    protected $engine;

    /**
     * @var TranslationFinder
     */
    protected $finder;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var array
     */
    protected $loaders;

    /**
     * Default constructor.
     * @param KernelInterface   $kernel            The kernel.
     * @param EngineInterface   $engine            The engine.
     * @param TranslationFinder $translationFinder The translation finder.
     * @param RouterInterface   $router            The router.
     * @param array             $loaders           An array of loaders.
     */
    public function __construct(KernelInterface $kernel, EngineInterface $engine, TranslationFinder $translationFinder, RouterInterface $router, $loaders = array())
    {
        $this->kernel  = $kernel;
        $this->engine  = $engine;
        $this->finder  = $translationFinder;
        $this->router  = $router;
        $this->loaders = array();
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
     * Dumps all translation files.
     *
     * @return null
     */
    public function dump()
    {
        /* Get exposeTranslationAction route */
        $route = $this->router->getRouteCollection()->get("bazinga_exposetranslation_js");
        $routeRequirements = $route->getRequirements();

        /* Get all format to generate */
        $formats = explode("|", $routeRequirements["_format"]);

        /* Get default format to generate symlink */
        $routeDefaults = $route->getDefaults();
        $defaultFormat = $routeDefaults["_format"];

        /* Get all translation files */
        $files = $this->finder->getAllResources();
        $messages = array();
        foreach ($files as $file) {
            $fileName = explode('.', $file->getFilename());
            $extension = end($fileName);
            $locale = prev($fileName);
            $domain = array();
            while (prev($fileName)) {
                $domain[] = current($fileName);
            }
            $domain = implode(".", $domain);

            if (isset($this->loaders[$extension])) {
                $catalogue = $this->loaders[$extension]->load($file, $locale, $domain);

                if (isset($messages[$locale])) {
                    $messages[$locale] = array_merge_recursive($messages[$locale], $catalogue->all());
                } else {
                    $messages[$locale] = $catalogue->all();
                }
            }
        }

        foreach ($messages as $locale => $domains) {
            foreach ($domains as $domain => $messageList) {
                foreach ($formats as $format) {
                    $content = $this->engine->render('BazingaExposeTranslationBundle::exposeTranslation.' . $format . '.twig', array(
                        'messages'        => array($domain => $messageList),
                        'locale'          => $locale,
                        'defaultDomains'  => $domain,
                    ));

                    $path[$format] = $this->kernel->getRootDir()."/../web" . strtr($route->getPattern(), array(
                        "{domain_name}" =>  $domain,
                        "{_locale}" => $locale,
                        "{_format}" => $format
                    ));

                    if (!is_dir(dirname($path[$format]))) {
                        mkdir(dirname($path[$format]), 0777, true);
                    }

                    if (file_exists($path[$format])) {
                        unlink($path[$format]);
                    }

                    file_put_contents($path[$format], $content);
                }

                if (!file_exists($symlink = ($this->kernel->getRootDir()."/../web" . strtr($route->getPattern(), array("{domain_name}" =>  $domain, "{_locale}" => $locale, ".{_format}" => "" ))))) {
                    symlink($path[$defaultFormat], $symlink);
                }
            }
        }
    }
}

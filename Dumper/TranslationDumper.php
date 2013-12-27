<?php

namespace Bazinga\ExposeTranslationBundle\Dumper;

use Bazinga\ExposeTranslationBundle\Finder\TranslationFinder;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Filesystem\Filesystem;

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
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Default constructor.
     * @param KernelInterface   $kernel            The kernel.
     * @param EngineInterface   $engine            The engine.
     * @param TranslationFinder $translationFinder The translation finder.
     * @param RouterInterface   $router            The router.
     * @param FileSystem        $filesystem        The file system.
     */
    public function __construct(
        KernelInterface $kernel,
        EngineInterface $engine,
        TranslationFinder $translationFinder,
        RouterInterface $router,
        Filesystem $filesystem
    ) {
        $this->kernel     = $kernel;
        $this->engine     = $engine;
        $this->finder     = $translationFinder;
        $this->router     = $router;
        $this->loaders    = array();
        $this->filesystem = $filesystem;
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
     * @param string  $targetDir Target directory.
     * @param boolean $symlink   True if generate symlink
     *
     * @return null
     */
    public function dump($targetDir = 'web', $symlink = false, $directory = null)
    {
        $route         = $this->router->getRouteCollection()->get('bazinga_exposetranslation_js');
        $directory     = null === $directory ? $this->kernel->getRootDir() . '/../' : $directory;

        $requirements  = $route->getRequirements();
        $formats       = explode('|', $requirements['_format']);

        $routeDefaults = $route->getDefaults();
        $defaultFormat = $routeDefaults['_format'];

        $parts = array_filter(explode('/', $route->getPattern()));
        $this->filesystem->remove($directory. $targetDir. "/" . current($parts));

        foreach ($this->getTranslationMessages() as $locale => $domains) {
            foreach ($domains as $domain => $messageList) {
                foreach ($formats as $format) {
                    $content = $this->engine->render('BazingaExposeTranslationBundle::exposeTranslation.' . $format . '.twig', array(
                        'messages'        => array($domain => $messageList),
                        'locale'          => $locale,
                        'defaultDomains'  => $domain,
                    ));

                    $path[$format] = $directory. $targetDir . strtr($route->getPattern(), array(
                        '{domain_name}' =>  $domain,
                        '{_locale}' => $locale,
                        '{_format}' => $format
                    ));

                    $this->filesystem->mkdir(dirname($path[$format]), 0777);

                    if (file_exists($path[$format])) {
                        $this->filesystem->remove($path[$format]);
                    }

                    file_put_contents($path[$format], $content);
                }

                $targetFile  = $directory . $targetDir;
                $targetFile .= strtr($route->getPattern(), array('{domain_name}' =>  $domain, '{_locale}' => $locale, '.{_format}' => '' ));

                if (true === $symlink) {
                    $this->filesystem->symlink($path[$defaultFormat], $targetFile);
                } else {
                    $this->filesystem->copy($path[$defaultFormat], $targetFile);
                }
            }
        }
    }

    /**
     * Get all translation messages
     *
     * @return array
     */
    protected function getTranslationMessages()
    {
        $messages = array();

        foreach ($this->finder->getAllResources() as $file) {
            $fileName  = explode('.', $file->getFilename());
            $extension = end($fileName);
            $locale    = prev($fileName);

            $domain = array();
            while (prev($fileName)) {
                $domain[] = current($fileName);
            }
            $domain = implode('.', $domain);

            if (isset($this->loaders[$extension])) {
                $catalogue = $this->loaders[$extension]->load($file, $locale, $domain);

                if (isset($messages[$locale])) {
                    $messages[$locale] = array_replace_recursive($messages[$locale], $catalogue->all());
                } else {
                    $messages[$locale] = $catalogue->all();
                }
            }
        }

        return $messages;
    }
}

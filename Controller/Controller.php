<?php

namespace Bazinga\ExposeTranslationBundle\Controller;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;

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
     * @var array
     */
    protected $loaders;

    protected $kernel;

    /**
     * Default constructor.
     *
     * @param \Symfony\Component\Translation\TranslatorInterface $translator    The translator.
     * @param \Symfony\Component\Templating\EngineInterface $engine             The engine.
     */
    public function __construct(TranslatorInterface $translator, EngineInterface $engine, $kernel)
    {
        $this->translator = $translator;
        $this->engine     = $engine;
        $this->kernel     = $kernel;
        $this->loaders    = array();
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
    public function exposeTranslationAction($domain_name, $_locale, $_format)
    {
        $finder = new Finder();

        $locations = array();
        foreach ($this->kernel->getBundles() as $bundle) {
            $locations[] = $bundle->getPath() . '/Resources';
        }
        $locations[] = $this->kernel->getRootDir() . '/Resources';

        $files = $finder->files()->name($domain_name . '.' . $_locale . '.*')->followLinks()->in($locations);

        $catalogues = array();
        foreach ($files as $file) {
            if (isset($this->loaders[$file->getExtension()])) {
                $catalogues[] = $this->loaders[$file->getExtension()]->load($file, $_locale, $domain_name);
            }
        }

        $messages = array();
        foreach ($catalogues as $catalogue) {
            $messages = array_merge($messages, $catalogue->all());
        }

        return new Response($this->engine->render('BazingaExposeTranslationBundle::exposeTranslation.' . $_format . '.twig', array(
            'messages'  => $messages,
            'locale'    => $_locale,
        )));
    }
}

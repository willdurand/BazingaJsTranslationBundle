<?php

namespace Bazinga\ExposeTranslationBundle\Controller;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Bazinga\ExposeTranslationBundle\Service\TranslationFinder;

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
     * Default constructor.
     *
     * @param \Symfony\Component\Translation\TranslatorInterface $translator                The translator.
     * @param \Symfony\Component\Templating\EngineInterface $engine                         The engine.
     * @param \Bazinga\ExposeTranslationBundle\Service\TranslationFinder $translationFinder The translation finder.
     * @param array $defaultDomains     An array of default domain names.
     */
    public function __construct(TranslatorInterface $translator, EngineInterface $engine,
        TranslationFinder $translationFinder, array $defaultDomains = array())
    {
        $this->translator        = $translator;
        $this->engine            = $engine;
        $this->translationFinder = $translationFinder;
        $this->defaultDomains    = $defaultDomains;
        $this->loaders           = array();
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
        $files = $this->translationFinder->getResources($domain_name, $_locale);

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
            'messages'        => $messages,
            'locale'          => $_locale,
            'defaultDomains'  => $this->defaultDomains,
        )));
    }
}

<?php

namespace Bazinga\ExposeTranslationBundle\Tests\Finder;

use Bazinga\ExposeTranslationBundle\Tests\WebTestCase;
use Symfony\Component\HttpKernel\Kernel;

/**
 * TranslationDumperTest
 *
 * @author Adrien Russo <adrien.russo.qc@gmail.com>
 */
class TranslationDumperTest extends WebTestCase
{
    private $translationDumper;
    private $translationFinder;
    private $route;
    private $filesystem;
    private $target;

    public function setUp()
    {
        if (version_compare(Kernel::MINOR_VERSION, '2.1.0', '<')) {
            $this->markTestSkipped();
        }

        $client    = static::createClient();
        $container = $client->getContainer();

        $this->target            = sys_get_temp_dir()."/bazinga/";
        $this->translationDumper = $container->get('bazinga.exposetranslation.dumper.translation_dumper');
        $this->translationFinder = $container->get('bazinga.exposetranslation.finder.translation_finder');
        $this->route             = $container->get('router')->getRouteCollection()->get('bazinga_exposetranslation_js');
        $this->filesystem        = $container->get('filesystem');

        if (function_exists('symlink')) {
            $this->translationDumper->dump('web', true, $this->target);
        } else {
            $this->translationDumper->dump('web', false, $this->target);
        }
    }

    /**
     * Test route parameters.
     *
     * @return null
     */
    public function testRoute()
    {
        $requirements = $this->route->getRequirements();
        $this->assertArrayHasKey('_format', $requirements);

        $routeDefaults = $this->route->getDefaults();
        $this->assertArrayHasKey('_format', $routeDefaults);
    }

    /**
     * Functional Test (Check if all file format are generated).
     *
     * @return null
     */
    public function testFileExistFormat()
    {
        $requirements = $this->route->getRequirements();
        $formats      = explode('|', $requirements['_format']);

        foreach ($formats as $format) {
            $this->assertFileExists($this->getTestPath($format));
        }
    }

    /**
     * Functional Test (Check if links are generated).
     *
     * @return null
     */
    public function testLinkExist()
    {
        $link = $this->target. 'web' . strtr($this->route->getPattern(), array(
            '{domain_name}' => 'messages',
            '{_locale}'     => 'en',
            '.{_format}'    => ''
        ));

        if (function_exists('symlink')) {
            $this->assertFileExists($link);
            $this->assertTrue(is_link($link));
        } else {
            $this->assertFileExists($link);
        }
    }

    /**
     * Get Test file path.
     *
     * @return string
     */
    protected function getTestPath($format)
    {
        return $this->target. 'web' . strtr($this->route->getPattern(), array(
            '{domain_name}' => 'messages',
            '{_locale}'     => 'en',
            '{_format}'     => $format
        ));
    }

    protected function tearDown()
    {
        if (is_dir($this->target)) {
            $this->filesystem->remove($this->target);
        }
    }
}

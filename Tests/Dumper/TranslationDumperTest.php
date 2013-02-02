<?php

namespace Bazinga\ExposeTranslationBundle\Tests\Finder;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

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

    protected function setUp()
    {
        $client = static::createClient();

        $this->target = sys_get_temp_dir()."/bazinga/";
        $this->translationDumper = $client->getContainer()->get('bazinga.exposetranslation.dumper.translation_dumper');
        $this->translationFinder = $client->getContainer()->get('bazinga.exposetranslation.finder.translation_finder');
        $this->route = $client->getContainer()->get('router')->getRouteCollection()->get('bazinga_exposetranslation_js');
        $this->filesystem = $client->getContainer()->get('filesystem');

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
        $routeRequirements = $this->route->getRequirements();
        $this->assertArrayHasKey('_format', $routeRequirements);

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
        $routeRequirements = $this->route->getRequirements();
        $formats = explode('|', $routeRequirements['_format']);
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
            '{domain_name}' =>  'message',
            '{_locale}' => 'en',
            '.{_format}' => ""
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
             '{domain_name}' =>  'message',
             '{_locale}' => 'en',
             '{_format}' => $format
         ));
    }

    protected function tearDown()
    {
        if (is_dir($this->target)) {
            $this->filesystem->remove($this->target);
        }
    }
}

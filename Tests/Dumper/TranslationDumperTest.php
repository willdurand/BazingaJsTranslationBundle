<?php

namespace Bazinga\ExposeTranslationBundle\Tests\Finder;

use Bazinga\ExposeTranslationBundle\Tests\WebTestCase;

/**
 * @author Adrien Russo <adrien.russo.qc@gmail.com>
 */
class TranslationDumperTest extends WebTestCase
{
    private $target;

    private $filesystem;

    private $dumper;

    public function setUp()
    {
        $client    = static::createClient();
        $container = $client->getContainer();

        $this->target     = '/tmp/test/'; //sys_get_temp_dir() . '/bazinga/expose-translation-bundle/';
        $this->filesystem = $container->get('filesystem');
        $this->dumper     = $container->get('bazinga.exposetranslation.dumper.translation_dumper');

        $this->filesystem->mkdir($this->target, 0755);
    }

    public function tearDown()
    {
        if (is_dir($this->target)) {
            //    $this->filesystem->remove($this->target);
        }
    }

    public function testDump()
    {
        $this->dumper->dump($this->target);

        foreach (array(
            'messages/en.js',
            'messages/en.json',
            'messages/fr.js',
            'messages/fr.json',
            'foo/en.js',
            'foo/en.json',
        ) as $file) {
            $this->assertFileExists($this->target . '/translations/' . $file);
        }

        $this->assertEquals(<<<JS
// fr
Translator.add("hello", "bonjour", "messages", "fr");

JS
        , file_get_contents($this->target . '/translations/messages/fr.js'));

        $this->assertEquals(<<<JS
// en
Translator.add("hello", "hello", "messages", "en");

JS
        , file_get_contents($this->target . '/translations/messages/en.js'));

        $this->assertEquals(<<<JS
Translator.fallback      = 'en';
Translator.defaultDomain = 'messages';

JS
        , file_get_contents($this->target . '/translations/config.js'));

        $this->assertEquals(<<<JSON
{
    "translations": {"fr":{"messages":{"hello":"bonjour"}}}
}

JSON
        , file_get_contents($this->target . '/translations/messages/fr.json'));

        $this->assertEquals(<<<JSON
{
    "translations": {"en":{"messages":{"hello":"hello"}}}
}

JSON
        , file_get_contents($this->target . '/translations/messages/en.json'));

        $this->assertEquals(<<<JSON
{
    "fallback": "en",
    "defaultDomain": "messages"
}

JSON
        , file_get_contents($this->target . '/translations/config.json'));
    }
}

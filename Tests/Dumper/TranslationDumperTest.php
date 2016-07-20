<?php

namespace Bazinga\JsTranslationBundle\Tests\Finder;

use Bazinga\Bundle\JsTranslationBundle\Tests\WebTestCase;

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
        $container = $this->getContainer();

        $this->target     = sys_get_temp_dir() . '/bazinga/js-translation-bundle';
        $this->filesystem = $container->get('filesystem');
        $this->dumper     = $container->get('bazinga.jstranslation.translation_dumper');

        $this->filesystem->mkdir($this->target);
    }

    public function tearDown()
    {
        if (is_dir($this->target)) {
            $this->filesystem->remove($this->target);
        }
    }

    public function testDumpAll()
    {
        $this->dumper->dump($this->target);

        foreach (array(
            'messages/en.js',
            'messages/en.json',
            'messages/fr.js',
            'messages/fr.json',
            'foo/en.js',
            'foo/en.json',
            'numerics/en.js',
            'numerics/en.json',
        ) as $file) {
            $this->assertFileExists($this->target . '/translations/' . $file);
        }

        foreach (array(
            'front/en.js',
            'front/en.json',
            'front/fr.js',
            'front/fr.json',
            'messages/es.js',
            'messages/es.json',
        ) as $file) {
            $this->assertFileNotExists($this->target . '/translations/' . $file);
        }

        $this->assertEquals(<<<JS
(function (Translator) {
    // fr
    Translator.add("hello", "bonjour", "messages", "fr");
})(Translator);

JS
        , file_get_contents($this->target . '/translations/messages/fr.js'));

        $this->assertEquals(<<<JS
(function (Translator) {
    // en
    Translator.add("hello", "hello", "messages", "en");
})(Translator);

JS
        , file_get_contents($this->target . '/translations/messages/en.js'));

        $this->assertEquals(<<<JS
(function (Translator) {
    Translator.fallback      = 'en';
    Translator.defaultDomain = 'messages';
})(Translator);

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

        $this->assertEquals(<<<JSON
{
    "translations": {"en":{"numerics":{"7":"Nos occasions","8":"Nous contacter","12":"pr\u00e9nom","13":"nom","14":"adresse","15":"code postal"}}}
}

JSON
        , file_get_contents($this->target . '/translations/numerics/en.json'));

    }

    public function testDumpAllMerged()
    {
        $this->dumper->dump($this->target, [], [], true);

        foreach (array(
                     'en/en.js',
                     'en/en.json',
                     'fr/fr.js',
                     'fr/fr.json',
                 ) as $file) {
            $this->assertFileExists($this->target . '/translations/' . $file);
        }

        foreach (array(
                     'es/es.js',
                     'es/es.json',
                 ) as $file) {
            $this->assertFileNotExists($this->target . '/translations/' . $file);
        }

        $this->assertEquals(<<<JS
(function (Translator) {
    // en
    Translator.add("foo", "bar", "foo", "en");
    Translator.add("hello", "hello", "messages", "en");
    Translator.add("7", "Nos occasions", "numerics", "en");
    Translator.add("8", "Nous contacter", "numerics", "en");
    Translator.add("12", "prénom", "numerics", "en");
    Translator.add("13", "nom", "numerics", "en");
    Translator.add("14", "adresse", "numerics", "en");
    Translator.add("15", "code postal", "numerics", "en");
})(Translator);

JS
            , file_get_contents($this->target . '/translations/en/en.js'));

        $this->assertEquals(<<<JS
(function (Translator) {
    // fr
    Translator.add("hello", "bonjour", "messages", "fr");
    Translator.add("7", "Nos occasions", "numerics", "fr");
    Translator.add("8", "Nous contacter", "numerics", "fr");
    Translator.add("12", "prénom", "numerics", "fr");
    Translator.add("13", "nom", "numerics", "fr");
    Translator.add("14", "adresse", "numerics", "fr");
    Translator.add("15", "code postal", "numerics", "fr");
})(Translator);

JS
            , file_get_contents($this->target . '/translations/fr/fr.js'));

        $this->assertEquals(<<<JS
(function (Translator) {
    Translator.fallback      = 'en';
    Translator.defaultDomain = 'messages';
})(Translator);

JS
            , file_get_contents($this->target . '/translations/config.js'));

        $this->assertEquals(<<<JSON
{
    "translations": {"en":{"foo":{"foo":"bar"},"messages":{"hello":"hello"},"numerics":{"7":"Nos occasions","8":"Nous contacter","12":"pr\u00e9nom","13":"nom","14":"adresse","15":"code postal"}}}
}

JSON
            , file_get_contents($this->target . '/translations/en/en.json'));

        $this->assertEquals(<<<JSON
{
    "translations": {"fr":{"messages":{"hello":"bonjour"},"numerics":{"7":"Nos occasions","8":"Nous contacter","12":"pr\u00e9nom","13":"nom","14":"adresse","15":"code postal"}}}
}

JSON
            , file_get_contents($this->target . '/translations/fr/fr.json'));

        $this->assertEquals(<<<JSON
{
    "fallback": "en",
    "defaultDomain": "messages"
}

JSON
            , file_get_contents($this->target . '/translations/config.json'));

    }

    public function testDumpEnJson()
    {
        $this->dumper->dump($this->target, ['en'], ['json']);

        foreach (array(
                     'messages/en.json',
                     'foo/en.json',
                     'numerics/en.json',
                 ) as $file) {
            $this->assertFileExists($this->target . '/translations/' . $file);
        }

        foreach (array(
                     'foo/en.js',
                     'front/en.js',
                     'front/en.json',
                     'front/fr.js',
                     'front/fr.json',
                     'messages/en.js',
                     'messages/es.js',
                     'messages/es.json',
                     'messages/fr.js',
                     'messages/fr.json',
                     'numerics/en.js',
                     'numerics/fr.js',
                     'numerics/fr.json',
                 ) as $file) {
            $this->assertFileNotExists($this->target . '/translations/' . $file);
        }

        $this->assertEquals(<<<JSON
{
    "translations": {"en":{"messages":{"hello":"hello"}}}
}

JSON
            , file_get_contents($this->target . '/translations/messages/en.json'));

        $this->assertEquals(<<<JSON
{
    "translations": {"en":{"numerics":{"7":"Nos occasions","8":"Nous contacter","12":"pr\u00e9nom","13":"nom","14":"adresse","15":"code postal"}}}
}

JSON
            , file_get_contents($this->target . '/translations/numerics/en.json'));

        $this->assertEquals(<<<JSON
{
    "fallback": "en",
    "defaultDomain": "messages"
}

JSON
            , file_get_contents($this->target . '/translations/config.json'));

    }

    public function testDumpEnJsMerged()
    {
        $this->dumper->dump($this->target, ['en'], ['js'], true);

        foreach (array(
                     'en/en.js',
                 ) as $file) {
            $this->assertFileExists($this->target . '/translations/' . $file);
        }

        foreach (array(
                     'en/en.json',
                     'es/es.js',
                     'es/es.json',
                     'fr/fr.js',
                     'fr/fr.json',
                 ) as $file) {
            $this->assertFileNotExists($this->target . '/translations/' . $file);
        }

        $this->assertEquals(<<<JS
(function (Translator) {
    // en
    Translator.add("foo", "bar", "foo", "en");
    Translator.add("hello", "hello", "messages", "en");
    Translator.add("7", "Nos occasions", "numerics", "en");
    Translator.add("8", "Nous contacter", "numerics", "en");
    Translator.add("12", "prénom", "numerics", "en");
    Translator.add("13", "nom", "numerics", "en");
    Translator.add("14", "adresse", "numerics", "en");
    Translator.add("15", "code postal", "numerics", "en");
})(Translator);

JS
            , file_get_contents($this->target . '/translations/en/en.js'));

        $this->assertEquals(<<<JS
(function (Translator) {
    Translator.fallback      = 'en';
    Translator.defaultDomain = 'messages';
})(Translator);

JS
            , file_get_contents($this->target . '/translations/config.js'));

    }

    public function testDumpFrJs()
    {
        $this->dumper->dump($this->target, ['fr'], ['js']);

        foreach (array(
                     'messages/fr.js',
                     'numerics/fr.js',
                 ) as $file) {
            $this->assertFileExists($this->target . '/translations/' . $file);
        }

        foreach (array(
                     'foo/en.js',
                     'foo/en.json',
                     'front/en.js',
                     'front/en.json',
                     'front/fr.js',
                     'front/fr.json',
                     'messages/en.js',
                     'messages/en.json',
                     'messages/es.js',
                     'messages/es.json',
                     'messages/fr.json',
                     'numerics/en.js',
                     'numerics/en.json',
                     'numerics/fr.json',
                 ) as $file) {
            $this->assertFileNotExists($this->target . '/translations/' . $file);
        }

        $this->assertEquals(<<<JS
(function (Translator) {
    // fr
    Translator.add("hello", "bonjour", "messages", "fr");
})(Translator);

JS
            , file_get_contents($this->target . '/translations/messages/fr.js'));

        $this->assertEquals(<<<JS
(function (Translator) {
    // fr
    Translator.add("7", "Nos occasions", "numerics", "fr");
    Translator.add("8", "Nous contacter", "numerics", "fr");
    Translator.add("12", "prénom", "numerics", "fr");
    Translator.add("13", "nom", "numerics", "fr");
    Translator.add("14", "adresse", "numerics", "fr");
    Translator.add("15", "code postal", "numerics", "fr");
})(Translator);

JS
            , file_get_contents($this->target . '/translations/numerics/fr.js'));

        $this->assertEquals(<<<JS
(function (Translator) {
    Translator.fallback      = 'en';
    Translator.defaultDomain = 'messages';
})(Translator);

JS
            , file_get_contents($this->target . '/translations/config.js'));

    }

    public function testDumpFrJsonMerge()
    {
        $this->dumper->dump($this->target, ['fr'], ['json'], true);

        foreach (array(
                     'fr/fr.json',
                 ) as $file) {
            $this->assertFileExists($this->target . '/translations/' . $file);
        }

        foreach (array(
                     'en/en.js',
                     'en/en.json',
                     'es/es.js',
                     'es/es.json',
                     'fr/fr.js',
                 ) as $file) {
            $this->assertFileNotExists($this->target . '/translations/' . $file);
        }

        $this->assertEquals(<<<JSON
{
    "translations": {"fr":{"messages":{"hello":"bonjour"},"numerics":{"7":"Nos occasions","8":"Nous contacter","12":"pr\u00e9nom","13":"nom","14":"adresse","15":"code postal"}}}
}

JSON
            , file_get_contents($this->target . '/translations/fr/fr.json'));

        $this->assertEquals(<<<JSON
{
    "fallback": "en",
    "defaultDomain": "messages"
}

JSON
            , file_get_contents($this->target . '/translations/config.json'));

    }
}

<?php

namespace Bazinga\JsTranslationBundle\Tests\Controller;

use Bazinga\Bundle\JsTranslationBundle\Tests\WebTestCase;

class ControllerTest extends WebTestCase
{
    private function requestTranslations($domain, $format, array $locales = array())
    {
        $requestUri = "/translations/{$domain}.{$format}";

        if (!empty($locales)) {
            $requestUri .= '?locales=' . implode(',', $locales);
        }

        return $this->sendRequest($requestUri);
    }

    public function testGetTranslations()
    {
        $response = $this->requestTranslations('messages', 'json');

        $this->assertEquals(<<<JSON
{
    "fallback": "en",
    "defaultDomain": "messages",
    "translations": {"en":{"messages":{"hello":"hello"}}}
}

JSON
        , $response->getContent());
    }

    public function testGetTranslationsWithMultipleLocales()
    {
        $response = $this->requestTranslations('messages', 'json', array('en', 'fr'));

        $this->assertEquals(<<<JSON
{
    "fallback": "en",
    "defaultDomain": "messages",
    "translations": {"en":{"messages":{"hello":"hello"}},"fr":{"messages":{"hello":"bonjour"}}}
}

JSON
        , $response->getContent());
    }

    public function testGetTranslationsWithUnknownDomain()
    {
        $response = $this->requestTranslations('unknown', 'json');

        $this->assertEquals(<<<JSON
{
    "fallback": "en",
    "defaultDomain": "messages",
    "translations": {"en":[]}
}

JSON
        , $response->getContent());
    }

    public function testGetTranslationsWithUnknownLocale()
    {
        $response = $this->requestTranslations('foo', 'json', array('pt'));

        $this->assertEquals(<<<JSON
{
    "fallback": "en",
    "defaultDomain": "messages",
    "translations": {"pt":[]}
}

JSON
        , $response->getContent());
    }

    public function testGetJsTranslations()
    {
        $response = $this->requestTranslations('messages', 'js');

        $this->assertEquals(<<<JS
(function (Translator) {
    Translator.fallback      = 'en';
    Translator.defaultDomain = 'messages';
    // en
    Translator.add("hello", "hello", "messages", "en");
})(Translator);

JS
        , $response->getContent());
    }

    public function testGetJsTranslationsWithMultipleLocales()
    {
        $response = $this->requestTranslations('messages', 'js', array('en', 'fr'));

        $this->assertEquals(<<<JS
(function (Translator) {
    Translator.fallback      = 'en';
    Translator.defaultDomain = 'messages';
    // en
    Translator.add("hello", "hello", "messages", "en");
    // fr
    Translator.add("hello", "bonjour", "messages", "fr");
})(Translator);

JS
        , $response->getContent());
    }

    public function testGetJsTranslationsWithUnknownDomain()
    {
        $response = $this->requestTranslations('unknown', 'js');

        $this->assertEquals(<<<JS
(function (Translator) {
    Translator.fallback      = 'en';
    Translator.defaultDomain = 'messages';
    // en
})(Translator);

JS
        , $response->getContent());
    }

    public function testGetJsTranslationsWithUnknownLocale()
    {
        $response = $this->requestTranslations('foo', 'js', array('pt'));

        $this->assertEquals(<<<JS
(function (Translator) {
    Translator.fallback      = 'en';
    Translator.defaultDomain = 'messages';
    // pt
})(Translator);

JS
        , $response->getContent());
    }

    public function testGetTranslationsWithNumericKeys()
    {
        $response = $this->requestTranslations('numerics', 'json', array('en'));

        $this->assertEquals(<<<JSON
{
    "fallback": "en",
    "defaultDomain": "messages",
    "translations": {"en":{"numerics":{"7":"Nos occasions","8":"Nous contacter","12":"pr\u00e9nom","13":"nom","14":"adresse","15":"code postal"}}}
}

JSON
        , $response->getContent());
    }

    public function testGetTranslationsWithPathTraversalAttack()
    {
        $client  = static::createClient();

        // 1. `evil.js` is not accessible
        $crawler  = $client->request('GET', '/translations?locales=en-randomstring/../../evil');
        $response = $client->getResponse();

        $this->assertEquals(404, $response->getStatusCode());

        // 2. let's create a random directory with a random js file
        // Fixing this issue = not creating any file here
        $crawler  = $client->request('GET', '/translations?locales=en-randomstring/something');
        $response = $client->getResponse();

        $this->assertFileNotExists(sprintf('%s/%s/messages.en-randomstring/something.js',
            $client->getKernel()->getCacheDir(),
            'bazinga-js-translation'
        ));

        // 3. path traversal attack
        // Fixing this issue = 404
        $crawler  = $client->request('GET', '/translations?locales=en-randomstring/../../evil');
        $response = $client->getResponse();

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testGetTranslationsWithLocaleInjection()
    {
        $client  = static::createClient();

        $crawler  = $client->request('GET', '/translations/messages.json?locales=foo%0Auncommented%20code;');
        $response = $client->getResponse();

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testGetTranslationsWithLowerCaseUnderscoredLocale()
    {
        $response = $this->requestTranslations('messages', 'json', array('de_lu'));

        $this->assertEquals(<<<JSON
{
    "fallback": "en",
    "defaultDomain": "messages",
    "translations": {"de_lu":{"messages":{"hello":"hallo"}}}
}

JSON
        , $response->getContent());
    }

    public function testGetTranslationsWithLowerCaseDashedLocale()
    {
        $response = $this->requestTranslations('messages', 'json', array('de-lu'));

        $this->assertEquals(<<<JSON
{
    "fallback": "en",
    "defaultDomain": "messages",
    "translations": {"de-lu":{"messages":{"hello":"hallo"}}}
}

JSON
        , $response->getContent());
    }

    public function testGetTranslationsWithUnderscoredLocale()
    {
        $response = $this->requestTranslations('messages', 'json', array('ro_RO'));

        $this->assertEquals(<<<JSON
{
    "fallback": "en",
    "defaultDomain": "messages",
    "translations": {"ro_RO":{"messages":{"hello":"alo"}}}
}

JSON
        , $response->getContent());
    }

    public function testGetTranslationsWithDashedLocale()
    {
        $response = $this->requestTranslations('messages', 'json', array('ro-RO'));

        $this->assertEquals(<<<JSON
{
    "fallback": "en",
    "defaultDomain": "messages",
    "translations": {"ro-RO":{"messages":{"hello":"alo"}}}
}

JSON
        , $response->getContent());
    }

    /**
     * @todo Rename this.
     */
    public static function providesUnsupportedLocaleTranslations()
    {
        return array(
            array(<<<END
{
    "fallback": "en",
    "defaultDomain": "messages",
    "translations": {"en_XX":{"messages":{"hello":"hello"}}}
}

END
                ,
                array('en_XX'),
            ),
            array(<<<END
{
    "fallback": "en",
    "defaultDomain": "messages",
    "translations": {"fr_XX":{"messages":{"hello":"bonjour"}}}
}

END
                ,
                array('fr_XX'),
            ),
            array(<<<END
{
    "fallback": "en",
    "defaultDomain": "messages",
    "translations": {"en-XX":{"messages":{"hello":"hello"}}}
}

END
                ,
                array('en-XX'),
            ),
            array(<<<END
{
    "fallback": "en",
    "defaultDomain": "messages",
    "translations": {"fr-XX":{"messages":{"hello":"bonjour"}}}
}

END
                ,
                array('fr-XX'),
            ),
        );
    }

    /**
     * @dataProvider providesUnsupportedLocaleTranslations
     */
    public function testGettranslationsactionReturnsTranslationsForTheLanguageCodeIfThereAreNoneForTheCountry(
        $expectedJson,
        $locales
    ) {
        $response = $this->requestTranslations('messages', 'json', $locales);

        $this->assertEquals($expectedJson, $response->getContent());
    }

    public function testDatetimeCanBeConstructedUsingARelativeDateTimeString()
    {
        $this->assertTrue(new \DateTime('+1 seconds') > new \DateTime());
    }

    public static function providesInvalidLocaleCodes()
    {
        return array(
            array(
                array('en_'),
            ),
            array(
                array('_GB'),
            ),
        );
    }

    /**
     * @dataProvider providesInvalidLocaleCodes
     */
    public function testGettranslationsactionReturnsA404IfARequestedLocaleCodeIsInvalid($invalidLocaleCodes)
    {
        $response = $this->requestTranslations('messages', 'json', $invalidLocaleCodes);

        $this->assertSame(404, $response->getStatusCode());
    }

    /**
     * Here we make sure that translations won't get in a pickle if there are multiple files for the same domain with 
     * related one-part and two-part locale codes.
     */
    public function testTranslationsForTwoPartLocaleCodesAreMergedIntoTheTranslationsForTheLanguageCode()
    {
        $response = $this->requestTranslations('messages', 'json', array('bo_IN'));

        $this->assertEquals(<<<JSON
{
    "fallback": "en",
    "defaultDomain": "messages",
    "translations": {"bo_IN":{"messages":{"one":"foo","two":"qux"}}}
}

JSON
        , $response->getContent());
    }
}

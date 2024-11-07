<?php

namespace Bazinga\Bundle\JsTranslationBundle\Tests;

use Bazinga\Bundle\JsTranslationBundle\Util;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\Translation\TranslatorInterface;

class UtilTest extends WebTestCase
{
    private $dumper;

    public function setUp(): void
    {
        $container = $this->getContainer();

        $this->target = sys_get_temp_dir() . '/bazinga/js-translation-bundle';
        $this->filesystem = new Filesystem();
        $this->dumper = $container->get('bazinga.jstranslation.translation_dumper');

        $this->filesystem->mkdir($this->target);
    }

    /**
     * @dataProvider provideCleanDomain
     */
    public function testCleanDomain(string $domain, string $expectedCleanDomain): void
    {
        $this->assertSame($expectedCleanDomain, Util::cleanDomain($domain));
    }

    public function provideCleanDomain(): iterable
    {
        yield ['messages', 'messages'];
        yield ['messages+intl-icu', 'messages'];
    }

    public function testGetMessagesFromTranslatorBag()
    {
        $translatorBag = $this->getContainer()->get('translator');

        $expectedTranslations = [
            'messages+intl-icu' => [
                'hello_name' => 'bonjour {name} !'
            ],
            'messages' => [
                'hello' => 'bonjour'
            ]
        ];

        $this->assertEquals(
            $expectedTranslations,
            Util::getMessagesFromTranslatorBag($translatorBag, 'fr', 'messages')
        );
        $this->assertEquals(
            $expectedTranslations,
            Util::getMessagesFromTranslatorBag($translatorBag, 'fr', 'messages+intl-icu')
        );
    }
}

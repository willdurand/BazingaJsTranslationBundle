<?php

namespace Bazinga\Bundle\JsTranslationBundle\Tests\Dumper;

use Bazinga\Bundle\JsTranslationBundle\Dumper\TranslationDumper;
use Bazinga\Bundle\JsTranslationBundle\Finder\TranslationFinder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;

class TranslationDumperFallbackLocaleTest extends TestCase
{
    private function createDumper(string $localeFallback, array $activeLocales): TranslationDumper
    {
        $twig = $this->createMock(Environment::class);
        $finder = $this->createMock(TranslationFinder::class);
        $filesystem = $this->createMock(Filesystem::class);

        return new TranslationDumper($twig, $finder, $filesystem, $localeFallback, 'messages', $activeLocales);
    }

    public function testFallbackLocaleIsAddedToActiveLocalesWhenMissing()
    {
        $dumper = $this->createDumper('en', ['fr']);

        $this->assertContains('en', $dumper->getActiveLocales());
        $this->assertContains('fr', $dumper->getActiveLocales());
    }

    public function testFallbackLocaleIsNotDuplicatedWhenAlreadyPresent()
    {
        $dumper = $this->createDumper('en', ['fr', 'en']);

        $this->assertEquals(['fr', 'en'], $dumper->getActiveLocales());
    }

    public function testFallbackLocaleIsNotAddedWhenActiveLocalesIsEmpty()
    {
        $dumper = $this->createDumper('en', []);

        $this->assertEmpty($dumper->getActiveLocales());
    }
}

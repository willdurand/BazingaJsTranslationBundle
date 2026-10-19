<?php

namespace Bazinga\Bundle\JsTranslationBundle\Tests\DependencyInjection;

use Bazinga\Bundle\JsTranslationBundle\DependencyInjection\BazingaJsTranslationExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BazingaJsTranslationExtensionTest extends TestCase
{
    public function testWarmupIsDisabledByDefaultAndDoesNotRestrictStaticDumps()
    {
        $container = $this->loadConfiguration(array(), array('en'));

        $this->assertFalse($container->getDefinition('bazinga.jstranslation.controller')->hasTag('kernel.cache_warmer'));
        $this->assertSame(array('en'), $container->getDefinition('bazinga.jstranslation.controller')->getArgument(8));
        $this->assertSame(array(), $container->getDefinition('bazinga.jstranslation.translation_dumper')->getArgument(5));
    }

    public function testWarmupCanBeEnabledWithSelectedFormats()
    {
        $container = $this->loadConfiguration(array('cache_warmer' => array('enabled' => true, 'formats' => array('json'))));
        $controller = $container->getDefinition('bazinga.jstranslation.controller');

        $this->assertTrue($controller->hasTag('kernel.cache_warmer'));
        $this->assertSame(array('json'), $controller->getArgument(10));
        $this->assertSame(array(), $controller->getArgument(8));
    }

    public function testWarmupCanBeExplicitlyDisabled()
    {
        $container = $this->loadConfiguration(array('cache_warmer' => false, 'active_locales' => array('fr')));
        $controller = $container->getDefinition('bazinga.jstranslation.controller');

        $this->assertFalse($controller->hasTag('kernel.cache_warmer'));
        $this->assertSame(array('js', 'json'), $controller->getArgument(10));
    }

    public function testEnabledWarmupDoesNotChangeStaticDumpLocaleSelection()
    {
        $container = $this->loadConfiguration(array('cache_warmer' => true), array('en'));

        $this->assertTrue($container->getDefinition('bazinga.jstranslation.controller')->hasTag('kernel.cache_warmer'));
        $this->assertSame(array('en'), $container->getDefinition('bazinga.jstranslation.controller')->getArgument(8));
        $this->assertSame(array(), $container->getDefinition('bazinga.jstranslation.translation_dumper')->getArgument(5));
    }

    public function testExplicitLocalesOverrideKernelEnabledLocales()
    {
        $container = $this->loadConfiguration(array('active_locales' => array('fr')), array('en'));

        $this->assertSame(array('fr'), $container->getDefinition('bazinga.jstranslation.controller')->getArgument(8));
        $this->assertSame(array('fr'), $container->getDefinition('bazinga.jstranslation.translation_dumper')->getArgument(5));
    }

    /**
     * @dataProvider invalidFormats
     */
    public function testInvalidWarmupFormatsAreRejected(array $formats)
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->loadConfiguration(array('cache_warmer' => array('enabled' => true, 'formats' => $formats)));
    }

    public function invalidFormats(): iterable
    {
        yield array(array('xml'));
        yield array(array());
    }

    private function loadConfiguration(array $config, ?array $enabledLocales = null): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', false);
        if (null !== $enabledLocales) {
            $container->setParameter('kernel.enabled_locales', $enabledLocales);
        }

        (new BazingaJsTranslationExtension())->load(array($config), $container);

        return $container;
    }
}

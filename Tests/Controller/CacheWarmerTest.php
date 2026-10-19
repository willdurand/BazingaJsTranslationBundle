<?php

namespace Bazinga\Bundle\JsTranslationBundle\Tests\Controller;

use Bazinga\Bundle\JsTranslationBundle\Controller\Controller;
use Bazinga\Bundle\JsTranslationBundle\Finder\TranslationFinder;
use Bazinga\Bundle\JsTranslationBundle\Tests\Fixtures\app\AppKernel;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerAggregate;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class CacheWarmerTest extends TestCase
{
    private $cacheDir;

    public function setUp(): void
    {
        $this->cacheDir = sys_get_temp_dir().'/bazinga-cache-warmer-'.uniqid();
    }

    public function tearDown(): void
    {
        (new Filesystem())->remove($this->cacheDir);
    }

    /**
     * @dataProvider warmupFormats
     */
    public function testWarmupGeneratesOnlyConfiguredFormats(array $formats)
    {
        $controller = $this->createController(array('en', 'fr'), array('messages', 'foo'), $formats);
        $this->assertSame(array(), $controller->warmUp($this->cacheDir));

        foreach (array('en', 'fr') as $locale) {
            foreach (array('messages', 'foo') as $domain) {
                foreach (array('js', 'json') as $format) {
                    $path = $this->cachePath($domain, $locale, $format);
                    $this->assertSame(in_array($format, $formats), file_exists($path), $path);
                }
            }
        }

        if (in_array('json', $formats)) {
            $data = json_decode(file_get_contents($this->cachePath('messages', 'fr', 'json')), true);
            $this->assertSame('bonjour', $data['translations']['fr']['messages']['hello']);
            $this->assertSame('bonjour {name} !', $data['translations']['fr']['messages+intl-icu']['hello_name']);
        }
    }

    public function warmupFormats(): iterable
    {
        yield array(array('js'));
        yield array(array('json'));
        yield array(array('js', 'json'));
    }

    public function testNoLocalesProducesNoCache()
    {
        $this->createController(array())->warmUp($this->cacheDir);

        $this->assertFalse(is_dir($this->cacheDir));
    }

    public function testEmptyDomainsUsesDefaultDomain()
    {
        $this->createController(array('en'), array(), array('json'), 'foo')->warmUp($this->cacheDir);

        $this->assertFileExists($this->cachePath('foo', 'en', 'json'));
        $this->assertFileNotExists($this->cachePath('messages', 'en', 'json'));
    }

    public function testEmptyDefaultDomainUsesMessages()
    {
        $this->createController(array('en'), array(), array('json'), '')->warmUp($this->cacheDir);

        $this->assertFileExists($this->cachePath('messages', 'en', 'json'));
    }

    public function testWarmupUsesSuppliedCacheDirectory()
    {
        $this->createController()->warmUp($this->cacheDir.'/temporary');

        $this->assertFileExists($this->cacheDir.'/temporary/bazinga-js-translation/messages.en.json');
        $this->assertFileNotExists($this->cachePath('messages', 'en', 'json'));
    }

    public function testRequestsReuseWarmedCache()
    {
        $finder = $this->createMock(TranslationFinder::class);
        $finder->expects($this->once())->method('get')->with('messages', 'en')
            ->willReturn(array(__DIR__.'/../Fixtures/translations/messages.en.yml'));
        $controller = $this->createController(array('en'), array('messages'), array('json'), 'messages', $finder);
        $controller->warmUp($this->cacheDir);

        $request = Request::create('/translations/messages.json');
        $request->setLocale('en');
        $response = $controller->getTranslationsAction($request, 'messages', 'json');

        $this->assertSame(file_get_contents($this->cachePath('messages', 'en', 'json')), $response->getContent());
        $this->assertSame('hello', json_decode($response->getContent(), true)['translations']['en']['messages']['hello']);
    }

    public function testRequestsCombiningLocalesRemainLazy()
    {
        $controller = $this->createController(array('en', 'fr'));
        $controller->warmUp($this->cacheDir);
        $this->assertFileNotExists($this->cachePath('messages', 'en-fr', 'json'));

        $request = Request::create('/translations/messages.json?locales=en,fr');
        $response = $controller->getTranslationsAction($request, 'messages', 'json');
        $translations = json_decode($response->getContent(), true)['translations'];

        $this->assertSame('hello', $translations['en']['messages']['hello']);
        $this->assertSame('bonjour', $translations['fr']['messages']['hello']);
        $this->assertFileExists($this->cachePath('messages', 'en-fr', 'json'));
    }

    public function testOptionalWarmupCanBeSkipped()
    {
        $controller = $this->createController();
        $this->assertTrue($controller->isOptional());
        (new CacheWarmerAggregate(array($controller)))->warmUp($this->cacheDir);

        $this->assertFalse(is_dir($this->cacheDir));
    }

    public function testSymfonyCacheWarmupCommandRunsConfiguredWarmer()
    {
        $kernel = new AppKernel('cache_warmup', false);
        $filesystem = new Filesystem();
        $filesystem->remove($kernel->getCacheDir());

        try {
            $application = new Application($kernel);
            $tester = new CommandTester($application->find('cache:warmup'));
            $this->assertSame(0, $tester->execute(array()));

            $path = $kernel->getCacheDir().'/bazinga-js-translation';
            $this->assertFileExists($path.'/messages.fr.json');
            $this->assertFileExists($path.'/numerics.fr.json');
            $this->assertFileNotExists($path.'/messages.fr.js');
            $data = json_decode(file_get_contents($path.'/messages.fr.json'), true);
            $this->assertSame('bonjour', $data['translations']['fr']['messages']['hello']);
        } finally {
            $kernel->shutdown();
            $filesystem->remove($kernel->getCacheDir());
        }
    }

    private function createController(
        array $locales = array('en'),
        array $domains = array('messages'),
        array $formats = array('json'),
        string $defaultDomain = 'messages',
        ?TranslationFinder $finder = null
    ): Controller {
        $loader = new FilesystemLoader();
        $loader->addPath(__DIR__.'/../../Resources/views', 'BazingaJsTranslation');
        if (null === $finder) {
            $finder = new TranslationFinder(array('all' => glob(__DIR__.'/../Fixtures/translations/*.yml')));
        }
        $controller = new Controller(
            new Translator('en'), new Environment($loader), $finder,
            $this->cacheDir.'/bazinga-js-translation', false, 'en', $defaultDomain, 86400,
            $locales, $domains, $formats
        );
        $controller->addLoader('yml', new YamlFileLoader());

        return $controller;
    }

    private function cachePath(string $domain, string $locale, string $format): string
    {
        return $this->cacheDir.'/bazinga-js-translation/'.$domain.'.'.$locale.'.'.$format;
    }
}

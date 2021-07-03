<?php

namespace Bazinga\Bundle\JsTranslationBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;
use Bazinga\Bundle\JsTranslationBundle\Tests\Fixtures\app\AppKernel;

abstract class WebTestCase extends BaseWebTestCase
{
    protected function deleteTmpDir()
    {
        if (!file_exists($dir = sys_get_temp_dir().'/'.Kernel::VERSION)) {
            return;
        }

        $fs = new Filesystem();
        $fs->remove($dir);
    }

    protected static function getContainer(): ContainerInterface
    {
        if (!static::$kernel) {
            static::$kernel = static::createKernel();
        }
        static::$kernel->boot();

        return static::$kernel->getContainer();
    }

    protected static function getKernelClass()
    {
        return AppKernel::class;
    }

    protected static function createKernel(array $options = array())
    {
        $class = self::getKernelClass();

        return new $class(
            'default',
            isset($options['debug']) ? $options['debug'] : true
        );
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->deleteTmpDir();
    }
}

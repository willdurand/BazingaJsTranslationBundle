<?php

namespace Bazinga\JsTranslationBundle\Tests\Command;

use Bazinga\Bundle\JsTranslationBundle\Command\DumpCommand;
use Bazinga\Bundle\JsTranslationBundle\Tests\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\Kernel;

/**
 * @covers \Bazinga\Bundle\JsTranslationBundle\Command\DumpCommand
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class DumpCommandTest extends WebTestCase
{
    private $transDumper;

    protected function setUp()
    {
        parent::setUp();
        $this->transDumper = $this->getMockBuilder('Bazinga\Bundle\JsTranslationBundle\Dumper\TranslationDumper')
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    public function providerExecute()
    {
        return [
            'No options supplied' => array(
                array(),
                $this->getTargetDir(),
                '/translations/{domain}.{_format}',
                array(),
                (object) array('domains' => null),
            ),
            'Target option supplied' => array(
                array('target' => 'foo'),
                'foo',
                '/translations/{domain}.{_format}',
                array(),
                (object) array('domains' => null),
            ),
            'Pattern option supplied' => array(
                array('--pattern' => '/foo/{_format}.{domain}'),
                $this->getTargetDir(),
                '/foo/{_format}.{domain}',
                array(),
                (object) array('domains' => null),
            ),
            'Format option supplied' => array(
                array('--format' => ['json']),
                $this->getTargetDir(),
                '/translations/{domain}.{_format}',
                array('json'),
                (object) array('domains' => null),
            ),
            'Merge domains option supplied' => array(
                array('--merge-domains' => true),
                $this->getTargetDir(),
                '/translations/{domain}.{_format}',
                array(),
                (object) array('domains' => true),
            ),
        ];
    }

    /**
     * @dataProvider providerExecute
     */
    public function testExecute($cli, $target, $pattern, $format, $mergeDomains)
    {
        $kernel = static::createKernel();
        $kernel->boot();

        $this->transDumper
            ->expects($this->once())
            ->method('dump')
            ->with($target, $pattern, $format, $mergeDomains)
        ;

        $application = new Application($kernel);
        $application->add(new DumpCommand($this->transDumper, $this->getKernelRoot()));

        $command = $application->find('bazinga:js-translation:dump');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()] + $cli);

        $output = $commandTester->getDisplay();
        $this->assertRegExp('#(Installing translation files in).*('.preg_quote($target, '/').' directory)#', $output);
    }

    private function getKernelRoot()
    {
        return sys_get_temp_dir().'/'.Kernel::VERSION;
    }

    private function getWebDir()
    {
        return class_exists('Symfony\Flex\Recipe') ? 'public' : 'web';
    }

    private function getTargetDir()
    {
        return $this->getKernelRoot() . '/../' . $this->getWebDir() . '/js';
    }
}

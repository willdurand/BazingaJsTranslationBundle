<?php

namespace Bazinga\ExposeTranslationBundle\Tests\Finder;

use Symfony\Component\Console\Input\InputArgument;
use Bazinga\ExposeTranslationBundle\Command\DumpCommand;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * DumpCommandTest
 *
 * @author Adrien Russo <adrien.russo.qc@gmail.com>
 */
class DumpCommandTest extends \PHPUnit_Framework_TestCase
{
    private $command;

    protected function setUp()
    {
        $this->command = new DumpCommand();
    }

    /**
     * Test if the definition has target argument.
     *
     * @return null
     */
    public function testHasArgumentTarget()
    {
        $this->assertTrue($this->command->getDefinition()->hasArgument('target'));
    }

    /**
     * Test if target argument default value is web.
     *
     * @return null
     */
    public function testDefaultTargetArgument()
    {
        $target = $this->command->getDefinition()->getArgument('target');
        $this->assertTrue($target instanceof InputArgument);
        $this->assertEquals('web', $target->getDefault());
    }

    /**
     * Test if the definition has symlink option.
     *
     * @return null
     */
    public function testHasOptionSymlink()
    {
        $this->assertTrue($this->command->getDefinition()->hasOption('symlink'));
    }

    /**
     * Test command inheritance.
     *
     * @return null
     */
    public function testInheritContainerAwareCommand()
    {
        $this->assertTrue($this->command instanceof ContainerAwareCommand);
    }
}

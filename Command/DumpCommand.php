<?php

namespace Bazinga\ExposeTranslationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * @author Adrien Russo <adrien.russo.qc@gmail.com>
 */
class DumpCommand extends ContainerAwareCommand
{
    /**
     * configure
     *
     * @return null
     */
    protected function configure()
    {
        $this
            ->setName('bazinga:expose:translation:dump')
            ->setDescription('Dumps all translation files');
    }

    /**
     * execute
     *
     * @param InputInterface  $input  The input interface
     * @param OutputInterface $output The output interface
     *
     * @return null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this
            ->getContainer()
            ->get("bazinga.exposetranslation.dumper.translation_dumper")
            ->dump();
    }
}

<?php

namespace Bazinga\Bundle\JsTranslationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Adrien Russo <adrien.russo.qc@gmail.com>
 */
class DumpCommand extends ContainerAwareCommand
{
    private $formats = ['js', 'json'];
    private $targetPath;

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('bazinga:js-translation:dump')
            ->setDefinition(array(
                new InputArgument(
                    'target',
                    InputArgument::OPTIONAL,
                    'Override the target directory to dump JS translation files in.'
                ),
            ))
            ->setDescription('Dumps all JS translation files to the filesystem')
            ->addOption(
                'merge',
                null,
                InputOption::VALUE_NONE,
                'If set, all domains will be merged into a single file per language'
            );

        foreach ($this->formats as $format) {
            $this->addOption(
                $format,
                null,
                InputOption::VALUE_NONE,
                "If set, only the $format files will be generated for every domain"
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->targetPath = $input->getArgument('target') ?:
            sprintf('%s/../web/js', $this->getContainer()->getParameter('kernel.root_dir'));
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dumpFormats = array();
        $dumpMerge = $input->getOption('merge');

        if (!is_dir($dir = dirname($this->targetPath))) {
            $output->writeln('<info>[dir+]</info>  ' . $dir);
            if (false === @mkdir($dir, 0777, true)) {
                throw new \RuntimeException('Unable to create directory ' . $dir);
            }
        }

        foreach ($this->formats as $format) {
            if ($input->getOption($format)) {
                $dumpFormats[] = $format;
            }
        }

        if (empty($dumpFormats)) {
            $dumpFormats = $this->formats;
        }

        $output->writeln(sprintf(
            'Installing translation files in <comment>%s</comment> directory',
            $this->targetPath
        ));

        $this
            ->getContainer()
            ->get('bazinga.jstranslation.translation_dumper')
            ->dump($this->targetPath, $dumpFormats, $dumpMerge);
    }
}

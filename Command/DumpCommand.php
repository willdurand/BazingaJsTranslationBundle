<?php
namespace Bazinga\ExposeTranslationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Config\Resource\FileResource;

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
            ->setName('bazingaexposetranslation:dump')
            ->setDescription('Dump all translation files')
            ->addArgument(
                'format',
                InputArgument::REQUIRED,
                'What format you want ?'
            );
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
        if (!$this->getContainer()->has('security.acl.provider')) {
            $output->writeln('You must setup the ACL system, see the Symfony2 documentation for how to do this.');

            return;
        }

        $loaders = $this->getContainer()->getParameter("bazinga.exposetranslation.loaders");
        $finder = $this->getContainer()->get("bazinga.exposetranslation.finder.translation_finder");
        $files = $finder->getAllResources();
        $route = $this->getContainer()->get("router")->getRouteCollection()->get("bazinga_exposetranslation_js");
        $routeRequirements = $route->getRequirements();
        $formats = explode("|", $routeRequirements["_format"]);
        $routeDefaults = $route->getDefaults();
        $defaultFormat = $routeDefaults["_format"];
        $messages = array();

        foreach ($files as $file) {
            $fileName = explode('.', $file->getFilename());
            $extension = end($fileName);
            $locale = prev($fileName);
            $domain = array();
            while (prev($fileName)) {
                $domain[] = current($fileName);
            }
            $domain = implode(".", $domain);

            if (isset($loaders[$extension])) {
                $catalogue = $this->getContainer()->get($loaders[$extension])->load($file, $locale, $domain);

                if (isset($messages[$locale])) {
                    $messages[$locale] = array_merge_recursive($messages[$locale], $catalogue->all());
                } else {
                    $messages[$locale] = $catalogue->all();
                }
            }
        }

        foreach ($messages as $locale => $domains) {
            foreach ($domains as $domain => $messageList) {
                foreach ($formats as $format) {
                    $content = $this->getContainer()->get('templating')->render('BazingaExposeTranslationBundle::exposeTranslation.' . $format . '.twig', array(
                        'messages'        => $messageList,
                        'locale'          => $locale,
                        'defaultDomains'  =>  $domain,
                    ));

                    $path[$format] = $this->getContainer()->get("kernel")->getRootDir()."/../web" . strtr($route->getPattern(), array(
                        "{domain_name}" =>  $domain,
                        "{_locale}" => $locale,
                        "{_format}" => $format
                    ));

                    if (!is_dir(dirname($path[$format]))) {
                        mkdir(dirname($path[$format]), 0755, true);
                    }

                    if (file_exists($path[$format])) {
                        unlink($path[$format]);
                    }

                    file_put_contents($path[$format], $content);
                }

                symlink($path[$defaultFormat], $this->getContainer()->get("kernel")->getRootDir()."/../web" . strtr($route->getPattern(), array(
                    "{domain_name}" =>  $domain,
                    "{_locale}" => $locale,
                    ".{_format}" => ""
                )));
            }
        }
    }
}

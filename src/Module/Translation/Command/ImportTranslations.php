<?php

namespace App\Module\Translation\Command;

use App\Module\Translation\TranslationManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

class ImportTranslations extends Command
{
    private $translations;

    public function __construct(TranslationManager $translations)
    {
        parent::__construct();
        $this->translations = $translations;
    }

    protected function configure() : void
    {
        $this
            ->setName('translation:import')
            ->setDescription('Import translations from a file to the database')
            ->addArgument('filename', InputArgument::REQUIRED, 'Filename')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        $data = Yaml::parseFile($input->getArgument('filename'));

        foreach ($data as $domain => $messages) {
            foreach ($messages as $id => $translations) {
                foreach ($translations as $locale => $translation) {
                    if (!empty($translation)) {
                        $this->translations->addMessage([
                            'domain' => $domain,
                            'locale' => $locale,
                            'id' => $id,
                            'translation' => $translation
                        ]);
                    }
                }
            }
        }

        $this->translations->flush();
        $output->writeln('Remember to flush cache afterwards!');
    }
}

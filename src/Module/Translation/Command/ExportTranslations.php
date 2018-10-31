<?php

namespace App\Module\Translation\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

class ExportTranslations extends Command
{
    private $db;
    private $fs;

    public function __construct(Connection $db, Filesystem $fs)
    {
        parent::__construct();

        $this->db = $db;
        $this->fs = $fs;
    }

    protected function configure() : void
    {
        $this
            ->setName('translation:export')
            ->setDescription('Export translations to a file')
            ->addArgument('filename', InputArgument::OPTIONAL, 'Filename', 'translations.dump.yaml')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        $result = $this->db->query('
            SELECT domain, locale, id, translation
            FROM translations
            WHERE translation IS NOT NULL
            ORDER BY domain, id, locale
        ');

        $data = [];

        foreach ($result as $row) {
            $data[$row['domain']][$row['id']][$row['locale']] = $row['translation'];
        }

        $this->fs->dumpFile($input->getArgument('filename'), Yaml::dump($data, 4));
    }
}

<?php

namespace App\Module\MigrationsV3\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Writes file metadata into the database. It did not exist at all in the previous version.
 */
class ConvertCustomData extends Command
{
    private $em;

    public function __construct(Connection $db)
    {
        parent::__construct();
        $this->db = $db;
    }

    protected function configure() : void
    {
        $this
            ->setName('migrations:convert-custom-data')
            ->setDescription('Convert old-format custom data.')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        $this->db->getConfiguration()->setSQLLogger(null);

        $libraries = $this->db->query('
            SELECT id, custom_data, array_agg(b.langcode) langcodes
            FROM organisations a
            INNER JOIN organisations_data b ON a.id = b.entity_id
            WHERE custom_data IS NOT NULL
            GROUP BY a.id
        ');

        $this->db->beginTransaction();

        $write = $this->db->prepare('
            UPDATE organisations
            SET custom_data = :custom_data
            WHERE id = :id
        ');

        foreach ($libraries as $row) {
            $entries = json_decode($row['custom_data']);
            $langcodes = explode(',', substr($row['langcodes'], 1, -1));

            if (!$entries) {
                continue;
            }

            foreach ($entries as $entry) {
                if (is_object($entry->title)) {
                    continue;
                }

                $entry->title = (object)['fi' => $entry->title];
                $entry->value = (object)['fi' => $entry->value];

                foreach ($langcodes as $langcode) {
                    if (!isset($entry->title->{$langcode})) {
                        $entry->title->{$langcode} = $entry->translations->{$langcode}->title ?? null;
                        $entry->value->{$langcode} = $entry->translations->{$langcode}->value ?? null;
                    }
                }

                unset($entry->translations);
            }

            $row['custom_data'] = json_encode($entries);

            unset($row['langcodes']);

            $write->execute($row);
        }

        $this->db->commit();
    }

    private function getImageSize(string $filename) : ?array
    {
        if ($data = getimagesize($filename)) {
            return array_slice($data, 0, 2);
        }

        return null;
    }
}

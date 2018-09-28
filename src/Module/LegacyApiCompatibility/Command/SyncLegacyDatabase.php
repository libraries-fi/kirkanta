<?php

namespace App\Module\LegacyApiCompatibility\Command;

use Doctrine\Common\Persistence\ConnectionRegistry;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
Use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncLegacyDatabase extends Command
{
    private $currentDb;
    private $legacyDb;
    private $em;

    public function __construct(Connection $current, Connection $legacy, EntityManagerInterface $manager)
    {
        parent::__construct();

        $this->currentDb = $current;
        $this->legacyDb = $legacy;
        $this->em = $manager;
    }

    protected function configure() : void
    {
        $this
            ->setName('legacy-db:sync')
            ->setDescription('Synchonize legacy database (for API v1, v2 and v3)')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        $this->syncLibraries();
    }

    private function syncLibraries() : void
    {
        $BATCH_SIZE = 10;
        $CACHE = (object)[];

        $this->legacyDb->beginTransaction();

        $this->synchronize('cities', 'cities', ['id', 'consortium_id'], function(&$row) {
            $row['region_id'] = 1003;
        });

        $this->synchronize('addresses', 'addresses', ['id', 'city_id', 'zipcode', 'box_number', 'coordinates'], function(&$row) use($CACHE) {
            $CACHE->coords[$row['id']] = $row['coordinates'];
            unset($row['coordinates']);
        });

        $this->legacyDb->commit();
    }

    private function readTranslations(array &$document, array $translation, array $fields) : array
    {
        $langcode = $translation['langcode'];
        $flipped = array_flip($fields);
        $document['translations'][$langcode] = array_intersect($translations, array_flip($fields));

        return $document;
    }

    private function mergePrimaryTranslation(array &$document, $langcode = 'fi') : array
    {
        $translations = json_decode($document['translations']);
        $document += $translations->fi;
        unset($translations->fi);
        $document->translations = json_encode($translations);
        return $document;
    }

    private function synchronize(string $current_table, string $legacy_table, array $fields, callable $mapper = null)
    {
        $read = read_query($this->currentDb, $current_table, $fields);

        foreach (result_iterator($read) as $document) {
            if ($mapper) {
                $mapper($document);
            }
            insert_query($this->legacyDb, $legacy_table, $document);
        }
    }
}

function merge_primary_translation(array &$document, $langcode = 'fi') : array {
    $document += $document['translations'][$langcode];
    unset($document['translations'][$langcode]);
    return $document;
}

function result_iterator(Statement $statement, array $values = [], $encode_translations = true) {
    $BATCH_SIZE = 100;
    $values['limit'] = $BATCH_SIZE;

    for ($i = 0; true; $i++) {
        $values['offset'] = $i * $BATCH_SIZE;
        $statement->execute($values);
        $found = false;

        while ($document = $statement->fetch()) {
            $found = true;

            if (isset($document['translations'])) {
                $document['translations'] = json_decode($document['translations'], true);
                $document = merge_primary_translation($document);

                if ($encode_translations) {
                    $document['translations'] = json_encode($document['translations']);
                }
            }

            yield $document;
        }

        if (!$found) {
            break;
        }
    }
}

function read_query(Connection $db, string $table, array $fields) : Statement {
    $fields = array_map(function($f) { return "a.{$f}"; }, $fields);
    $fields = implode(', ', $fields);

    $sql = "
        SELECT
            {$fields},
            jsonb_object_agg(t.langcode, to_jsonb(t) - 'langcode' - 'entity_id') AS translations
        FROM {$table} a
        INNER JOIN {$table}_data t ON a.id = t.entity_id
        GROUP BY a.id
        ORDER BY a.id
        LIMIT :limit
        OFFSET :offset
    ";

    return $db->prepare($sql);
}

function insert_query(Connection $db, string $table, array $values) : void {
    $fields = array_keys($values);
    $placeholders = array_map(function($f) { return ":{$f}"; }, $fields);
    $updates = array_map(function($f) { return "{$f} = EXCLUDED.{$f}"; }, $fields);

    $fields = implode(', ', $fields);
    $placeholders = implode(', ', $placeholders);
    $updates = implode(', ', $updates);

    $sql = "
        INSERT INTO {$table} ($fields) VALUES ({$placeholders})
        ON CONFLICT (id)
        DO
        UPDATE SET {$updates}
    ";

    $db->executeQuery($sql, $values);
}

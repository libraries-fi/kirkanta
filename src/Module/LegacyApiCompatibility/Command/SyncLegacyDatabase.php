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
    private $cache;

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
        $this->cache = new \stdClass;
        $this->syncLibraries();
    }

    private function syncLibraries() : void
    {
        $smtContact = $this->currentDb->prepare('
            SELECT type, contact
            FROM contact_info
            WHERE id IN (?, ?)
        ');

        $this->legacyDb->beginTransaction();

        // $this->synchronize('cities', 'cities', ['id', 'consortium_id'], function(&$row) {
        //     // Set a fallback value because API users don't really care about this,
        //     // so we don't bother syncing regions.
        //     $row['region_id'] = 1003;
        // });
        //
        // $this->synchronize('addresses', 'addresses', ['id', 'city_id', 'zipcode', 'box_number', 'coordinates'], function(&$row) {
        //     // In legacy DB, coordinates exist in organisations table.
        //     $this->cache->coords[$row['id']] = $row['coordinates'];
        //     unset($row['coordinates']);
        // });

        $this->synchronize('organisations', 'organisations', [
            'role',
            // 'type',
            'id',
            'group_id',
            'city_id',
            'address_id',
            'mail_address_id',
            'founded',
            'isil',
            'identificator',
            'construction_year',
            'building_architect',
            'interior_designer',
            'created',
            'modified',
            'state',
        ], function(&$row) use($smtContact) {
            $role = $row['role'];
            unset($row['role']);

            if (!in_array($role, ['library', 'foreign'])) {
                throw new SkipSynchronizationException;
            }

            foreach ($row['translations'] as $langcode => &$data) {
                /*
                 * Contact details are now entity references vs. strings in old DB.
                 * Also, there only exist fields for email and homepage, NOT phone.
                 */
                $smtContact->execute([
                    $data['email_id'] ?: 0,
                    $data['homepage_id'] ?: 0,
                ]);

                foreach ($smtContact->fetchAll() as $contact) {
                    $keys = [
                        'email' => 'email',
                        'website' => 'homepage',
                    ];
                    $data[$keys[$contact['type']]] = $contact['contact'];
                }

                unset($data['email_id']);
                unset($data['homepage_id']);
                unset($data['phone_id']);
                unset($data['slug']);
            }
        });

        $this->legacyDb->commit();
    }

    private function synchronize(string $current_table, string $legacy_table, array $fields, callable $mapper = null)
    {
        $read = read_query($this->currentDb, $current_table, $fields);

        foreach (result_iterator($read, [], false) as $document) {
            try {
                if ($mapper) {
                    $mapper($document);
                }

                $document = merge_primary_translation($document);
                $document['translations'] = json_encode($document['translations']);
                insert_query($this->legacyDb, $legacy_table, $document);
            } catch (SkipSynchronizationException $e) {
                // pass
            }
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
                // $document = merge_primary_translation($document);

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
            id,
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
    if (empty($values['id'])) {
        throw new \InvalidArgumentException('Document ID is required');
    }

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

class SkipSynchronizationException extends \Exception
{

}

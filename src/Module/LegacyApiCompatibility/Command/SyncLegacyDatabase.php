<?php

namespace App\Module\LegacyApiCompatibility\Command;

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
    private $cache;

    const GROUP_NOBODY = 2;

    public function __construct(Connection $current, Connection $legacy)
    {
        parent::__construct();

        $this->currentDb = $current;
        $this->legacyDb = $legacy;
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

        // $this->syncConsortiums();
        // $this->syncLibraries();

        // $this->syncServices();

        // $this->syncStaff();

        $this->syncPeriods();
    }

    private function syncConsortiums() : void
    {
        $this->legacyDb->beginTransaction();

        $this->synchronize('consortiums', 'consortiums', [
            'id',
            'logo',
            'created',
            'modified',
            'state',
        ]);

        $cache = new \stdClass;

        $result = $this->currentDb->query('
            SELECT
                parent_id,
                library_id,
                service_point_id
            FROM finna_service_point_bindings
        ');

        foreach ($result as $row) {
            $cache->bindings[$row['parent_id']] = $row['library_id'] ?? $row['service_point_id'];
        }

        $this->synchronize('finna_additions', 'finna_consortium_data', [
            'finna_id',
            'finna_coverage',
            'exclusive',
        ], function(&$row) use($cache) {
            $row['service_point_id'] = $cache->bindings[$row['id']] ?? null;

            $cache->exclusive[$row['id']] = $row['exclusive'];
            unset($row['exclusive']);

            $row['consortium_id'] = $row['id'];
            unset($row['id']);

        }, [
            'insert_id' => 'consortium_id'
        ]);

        $smtExclusive = $this->legacyDb->prepare('UPDATE consortiums SET special = ? WHERE id = ?');

        foreach ($cache->exclusive as $id => $state) {
            $smtExclusive->execute([$state ? 't' : 'f', $id]);
        }

        $this->legacyDb->commit();
    }

    private function syncLibraries() : void
    {
        $smtContact = $this->currentDb->prepare('
            SELECT type, contact
            FROM contact_info
            WHERE id IN (?, ?)
        ');

        $this->legacyDb->beginTransaction();

        $this->synchronize('cities', 'cities', ['id', 'consortium_id'], function(&$row) {
            // Set a fallback value because API users don't really care about this,
            // so we don't bother syncing regions.
            $row['region_id'] = 1003;
        });

        $this->synchronize('addresses', 'addresses', ['id', 'city_id', 'zipcode', 'box_number', 'coordinates'], function(&$row) {
            // In legacy DB, coordinates exist in organisations table.
            $this->cache->coords[$row['id']] = $row['coordinates'];
            unset($row['coordinates']);
        });

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

            $row['coordinates'] = $this->cache->coords[$row['address_id']] ?? null;

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

    private function syncServices() : void
    {
        // NOTE: Pay attention to confusing table names!

        $this->legacyDb->beginTransaction();

        $this->synchronize('services', 'service_types', [
            'type',
            'created',
            'modified',
        ]);

        $this->synchronize('service_instances', 'services_new', [
            'parent_id',
            'template_id',
            'picture',
            'for_loan',
            'phone_number',
            'email',
            'created',
            'modified',
            'shared',
        ], function(&$row) {
            $row['organisation_id'] = $row['parent_id'];
            unset($row['parent_id']);

            $row['for_loan'] = $row['for_loan'] ? 't' : 'f';
            $row['shared'] = $row['shared'] ? 't' : 'f';
        });

        $this->legacyDb->commit();
    }

    private function syncStaff() : void
    {
        $this->legacyDb->beginTransaction();

        $this->synchronize('persons', 'persons', [
            'first_name',
            'last_name',
            'qualities',
            'email',
            'email_public',
            'phone',
            'url',
            'is_head',
            'created',
            'modified',
            'library_id',
            'state'
        ], function(&$row) {
            $row['organisation_id'] = $row['library_id'];
            unset($row['library_id']);

            $row['email_public'] = $row['email_public'] ? 't' : 'f';
            $row['is_head'] = $row['is_head'] ? 't' : 'f';

            $row['group_id'] = self::GROUP_NOBODY;
        });

        $this->legacyDb->commit();
    }

    private function syncPeriods() : void
    {
        $smtRead = $this->currentDb->prepare('
            SELECT
                id,
                parent_id,
                valid_from,
                valid_until,
                created,
                modified,
                days,
                jsonb_object_agg(t.langcode, to_jsonb(t) - \'langcode\' - \'entity_id\') AS translations
            FROM periods a
            INNER JOIN periods_data t ON a.id = t.entity_id
            WHERE COALESCE(a.valid_until, NOW()) >= NOW()
                AND a.parent_id IS NOT NULL
                AND section = \'default\' -- THIS FIELD WILL BE DROPPED ON DB UPGRADE

                AND a.id = 299673
            GROUP BY a.id
            ORDER BY a.id
            LIMIT :limit
            OFFSET :offset
        ');

        foreach (result_iterator($smtRead) as $row) {
            $row['days'] = json_decode($row['days']);
            $row['translations'] = json_decode($row['translations']);
            var_dump($row);
            exit;
        }
    }

    private function synchronize(string $current_table, string $legacy_table, array $fields, callable $mapper = null, array $options = [])
    {
        $insert_id = $options['insert_id'] ?? 'id';

        $read = read_query($this->currentDb, $current_table, $fields);

        foreach (result_iterator($read, [], false) as $document) {
            try {
                if ($mapper) {
                    $mapper($document);
                }

                $document = merge_primary_translation($document);
                $document['translations'] = json_encode($document['translations']);
                insert_query($this->legacyDb, $legacy_table, $document, $insert_id);
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

function insert_query(Connection $db, string $table, array $values, string $id_field = 'id') : void {
    if (empty($values[$id_field])) {
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
        ON CONFLICT ({$id_field})
        DO
        UPDATE SET {$updates}
    ";

    $db->executeQuery($sql, $values);
}

class SkipSynchronizationException extends \Exception
{

}
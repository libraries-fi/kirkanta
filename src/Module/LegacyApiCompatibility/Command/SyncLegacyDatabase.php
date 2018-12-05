<?php

namespace App\Module\LegacyApiCompatibility\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
Use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncLegacyDatabase extends Command
{
    private $currentDb;
    private $legacyDb;
    private $cache;

    public static $TRLANGS = ['en', 'ru', 'se', 'sv'];

    const GROUP_NOBODY = 2;
    const SELFSERVICE_PERIOD_INCREMENT = 10000000;

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
            ->addArgument('entity_type', InputArgument::REQUIRED, 'Entity type')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        $this->currentDb->getConfiguration()->setSQLLogger(null);
        $this->legacyDb->getConfiguration()->setSQLLogger(null);

        $this->cache = new \stdClass;

        $entity_type = $input->getArgument('entity_type');

        switch ($entity_type) {
            case 'consortium':
                $this->syncConsortiums();
                break;

            case 'library':
                $this->syncLibraries();
                break;

            case 'service':
                $this->syncServices();
                break;

            case 'person':
                $this->syncStaff();
                break;

            case 'period':
                $this->syncPeriods($output);
                break;

            default:
                throw new \Exception('Invalid entity type');
        }
    }

    private function syncConsortiums() : void
    {
        $this->legacyDb->beginTransaction();

        $this->synchronize('consortiums', 'consortiums', [
            'id',
            // 'logo',
            'created',
            'modified',
            'state',
            'default_langcode',
            'logo_id'
        ], function(&$row) {
            if ($row['logo_id']) {
                $logoname = $this->currentDb->query("SELECT filename FROM pictures WHERE id = {$row['logo_id']}")->fetchColumn();
                $row['logo'] = $logoname;
            }

            unset($row['logo_id']);
        });

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
            'default_langcode'
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

        $this->synchronize('cities', 'cities', ['id', 'consortium_id', 'default_langcode'], function(&$row) {
            // Set a fallback value because API users don't really care about this,
            // so we don't bother syncing regions.
            $row['region_id'] = 1003;
        });

        $this->synchronize('addresses', 'addresses', ['id', 'city_id', 'zipcode', 'box_number', 'ST_AsText(coordinates) AS coordinates'], function(&$row) {

            if ($row['coordinates']) {
                list($lon, $lat) = explode(' ', substr($row['coordinates'], 6, -1));
                $row['coordinates'] = "{$lat}, {$lon}";
            }

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
            'default_langcode',
            'custom_data',
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

            $custom_data = json_decode($row['custom_data']);

            if ($custom_data) {
                foreach ($custom_data as $entry) {
                    $entry->translations = (object)[];
                    foreach (self::$TRLANGS as $langcode) {
                        $entry->translations->{$langcode} = (object)[
                            'title' => $entry->title->{$langcode} ?? null,
                            'value' => $entry->value->{$langcode} ?? null,
                        ];
                    }
                    $entry->title = $entry->title->fi ?? null;
                    $entry->value = $entry->value->fi ?? null;
                }

                $row['custom_data'] = json_encode($custom_data);
            }
        });

        $this->legacyDb->query('DELETE FROM pictures WHERE organisation_id IS NOT NULL');

        $this->synchronize('pictures', 'pictures', ['id', 'filename', 'created', 'parent_id', 'cover'], function(&$row) {
            $row['organisation_id'] = $row['parent_id'];
            unset($row['parent_id']);

            $row['is_default'] = sprintf('%d', $row['cover']);
            unset($row['cover']);

            foreach ($row['translations'] as $langcode => &$data) {
              unset($data['entity_type']);
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
            'default_langcode'
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
            'default_langcode'
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
            'state',
            'default_langcode'
        ], function(&$row) {
            $row['organisation_id'] = $row['library_id'];
            unset($row['library_id']);

            $row['email_public'] = $row['email_public'] ? 't' : 'f';
            $row['is_head'] = $row['is_head'] ? 't' : 'f';

            $row['group_id'] = self::GROUP_NOBODY;
        });

        $this->legacyDb->commit();
    }

    private function syncPeriods(OutputInterface $output) : void
    {
        $splitSelfService = function(array $period) {
            $regular = $period;
            $regular['section'] = 'default';

            $self = $period;
            $self['id'] += self::SELFSERVICE_PERIOD_INCREMENT;
            // $self['days'] = json_decode(json_encode($self['days']));
            $self['days'] = [];
            $self['section'] = 'selfservice';
            $self['description'] = null;

            $hasSelf = false;

            foreach ($regular['days'] as $i => $day) {
                $self['days'][$i] = (object)[
                    'times' => [],
                    'closed' => true,
                    'opens' => null,
                    'closes' => null,
                ];

                foreach (self::$TRLANGS as $langcode) {
                    if (!isset($day->translations->{$langcode})) {
                        if (!isset($day->translations)) {
                            $day->translations = (object)[];
                        }
                        $day->translations->{$langcode} = (object)['info' => null];
                    }
                }
                if (is_object($day->info)) {
                    $day->info = $day->info->fi ?? null;
                }
                if (!empty($day->times)) {
                    if (is_object($day->times)) {
                        // Unsaved imported periods have an stdClass in place of an array.
                        $day->times = get_object_vars($day->times);
                    }
                    foreach ($day->times as $j => $time) {
                        if (isset($time->staff) && !$time->staff) {
                            if (empty($self['days'][$i]->times)) {
                                $self['days'][$i]->times = [(object)[
                                    'opens' => reset($day->times)->opens,
                                    'closes' => end($day->times)->closes,
                                ]];
                                $self['days'][$i]->opens = reset($day->times)->opens;
                                $self['days'][$i]->opens = end($day->times)->closes;
                                $self['days'][$i]->closed = false;
                            }

                            // Self-service times will contain only one entry.
                            unset($day->times[$j]);
                        }

                        unset($time->staff);
                    }
                    $day->times = array_values($day->times);
                }
            }

            return [$regular, $self];
        };

        $smtRead = $this->currentDb->prepare('
            SELECT
                id,
                parent_id AS organisation_id,
                valid_from,
                valid_until,
                created,
                modified,
                days,
                (valid_until IS NULL)::int AS continuous,
                0 AS shared,
                jsonb_object_agg(t.langcode, to_jsonb(t) - \'langcode\' - \'entity_id\') AS translations,
                is_legacy_format
            FROM periods a
            INNER JOIN periods_data t ON a.id = t.entity_id
            WHERE COALESCE(a.valid_until, CURRENT_DATE) >= CURRENT_DATE
                AND a.parent_id IS NOT NULL
                AND a.section = \'default\' -- THIS FIELD WILL BE DROPPED ON DB UPGRADE

                -- AND a.id = 299673 -- DEBUG STUFF
                -- AND a.parent_id = 86600 -- DEBUG STUFF
            GROUP BY a.id
            ORDER BY a.id
            LIMIT :limit
            OFFSET :offset
        ');

        $dropLibraries = [];
        $libraryPeriods = [];

        foreach (result_iterator($smtRead) as $row) {
            $row['days'] = json_decode($row['days']);

            // Utility functions require decoding as arrays
            $row['translations'] = json_decode($row['translations'], true);

            if ($row['is_legacy_format']) {
                // Don't touch libraries that still have legacy periods.
                $dropLibraries[] = $row['organisation_id'];

                $output->writeln("Library #{$row['organisation_id']} has legacy period #{$row['id']}");
            } else {
                unset($row['is_legacy_format']);

                list($regular, $self) = $splitSelfService($row);
                merge_primary_translation($regular);

                $regular['days'] = json_encode($regular['days']);
                $regular['translations'] = json_encode($regular['translations']);

                // insert_query($this->legacyDb, 'periods', $regular);
                $libraryPeriods[$row['organisation_id']][] = $regular;

                if ($self) {
                    merge_primary_translation($self);

                    $self['days'] = json_encode($self['days']);
                    $self['translations'] = json_encode($self['translations']);

                    // insert_query($this->legacyDb, 'periods', $self);
                    $libraryPeriods[$row['organisation_id']][] = $self;
                }
            }
        }

        $libraryPeriods = array_diff_key($libraryPeriods, array_flip($dropLibraries));

        foreach ($libraryPeriods as $id => $periods) {
            $this->legacyDb->beginTransaction();

            $smtDeleteOld = $this->legacyDb->prepare('
                DELETE
                FROM periods
                WHERE organisation_id = :library
            ');
            $smtDeleteOld->execute(['library' => $id]);

            foreach ($periods as $period) {
                insert_query($this->legacyDb, 'periods', $period);
            }

            $this->legacyDb->commit();
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

function merge_primary_translation(array &$document) : array {

    $langcode = $document['default_langcode'] ?? 'fi';
    $document += $document['translations'][$langcode] ?? [];
    unset($document['translations'][$langcode]);
    unset($document['default_langcode']);
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
    $fields = array_map(function($f) {
        if (strpos($f, ' AS ')) {
            return $f;
        } else {
            return "a.{$f}";
        }
    }, $fields);
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

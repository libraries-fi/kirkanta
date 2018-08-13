<?php

namespace App\Module\ApiCache;

use App\Entity\Feature\Translatable;
use App\EntityTypeManager;
use App\Module\ApiCache\Entity\Feature\ApiCacheable;
use Doctrine\DBAL\Driver\Connection;
use OutOfBoundsException;
use Symfony\Component\Serializer\SerializerInterface;

class DocumentManager
{
    private $types;
    private $database;
    private $serializer;

    private $enabled = [
        'library',
        'service',
    ];

    public function __construct(EntityTypeManager $types, Connection $database, SerializerInterface $serializer)
    {
        $this->types = $types;
        $this->database = $database;
        $this->serializer = $serializer;
    }

    public function getConnection() : Connection
    {
        return $this->database;
    }

    public function getEntityTypeManager() : EntityTypeManager
    {
        return $this->types;
    }

    public function write(ApiCacheable $entity)
    {
        try {
            list($type_id, $document, $translations) = $this->serialize($entity);

            // $this->database->beginTransaction();

            // $smt = $this->database->prepare('
            //     DELETE
            //     FROM api_documents
            //     WHERE
            //     type = :type AND
            //     id = :id
            // ');
            //
            // $smt->execute([
            //     'type' => $type_id,
            //     'id' => $entity->getId(),
            // ]);
            //
            // $smt = $this->database->prepare('
            //     INSERT
            //     INTO api_documents (
            //         type,
            //         id,
            //         body,
            //         translations
            //     )
            //     VALUES (:type, :id, :values, :translations)
            // ');
            //
            // $smt->execute([
            //     'type' => $type_id,
            //     'id' => $entity->getId(),
            //     'values' => $document,
            //     'translations' => $translations,
            // ]);

            $entity_class = $this->types->getEntityClass($type_id);

            $dql = sprintf('UPDATE %s e SET e.cached_document = :document WHERE e.id = :id', $entity_class);

            $query = $this->types->getEntityManager()->createQuery($dql);
            $query->execute([
                'document' => $document,
                'id' => $entity->getId(),
            ]);


            // $table = $this->types->getEntityManager()->getRepository($entity_class)->getClassMetadata()->getTableName();
            //
            // var_dump($table);
            // exit;
            //
            // $smt = $this->database->prepare('
            //     UPDATE organisations
            //     SET cached_document = :document
            //     WHERE id = :id
            // ');
            //
            // $smt->execute([
            //     'document' => $document,
            //     'id' => $entity->getId()
            // ]);

            // $this->database->commit();
        } catch (OutOfBoundsException $e) {
            // Unmanaged entity type.

            exit('fail');
        }
    }

    protected function serialize($entity) : array
    {
        // $GLOBALS['API_SERIALIZE_MODE'] = 'cache';

        $context = ['groups' => ['default', 'api_cache']];
        $normalized = $this->serializer->normalize($entity, 'json', $context);
        $values = $this->serializer->serialize($entity, 'json', $context);

        // unset($GLOBALS['API_SERIALIZE_MODE']);

        if ($entity instanceof Translatable) {
            $translations = [];

            foreach ($entity->getTranslations() as $langcode => $data) {
                $translations[$langcode] = $this->serializer->normalize($data, 'json', $context);
            }

            $translations = $this->serializer->encode($translations, 'json');
        }

        $class_name = get_class($entity);
        $type_id = $this->types->getTypeId($class_name);

        return [$type_id, $values, null];
        return [$type_id, $values, $translations];

        // $document = $this->serializer->encode($normalized, 'json', $context);

        // return $document;
    }
}

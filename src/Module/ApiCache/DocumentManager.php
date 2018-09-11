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
            $entity_class = $this->types->getEntityClass($type_id);
            $dql = "UPDATE {$entity_class} e SET e.cached_document = :document WHERE e.id = :id";

            $query = $this->types->getEntityManager()->createQuery($dql);
            $query->execute([
                'document' => $document,
                'id' => $entity->getId(),
            ]);
        } catch (OutOfBoundsException $e) {
            // Unmanaged entity type.
            exit('fail');
        }
    }

    protected function serialize($entity) : array
    {
        $context = ['groups' => ['default', 'api_cache']];
        $normalized = $this->serializer->normalize($entity, 'json', $context);

        print_r($normalized);
        
        $values = $this->serializer->serialize($entity, 'json', $context);

        $class_name = get_class($entity);
        $type_id = $this->types->getTypeId($class_name);

        return [$type_id, $values, null];
    }
}

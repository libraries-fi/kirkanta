<?php

namespace App\Module\ApiCache;

use App\Entity\Feature\Translatable;
use App\Module\ApiCache\Entity\Feature\ApiCacheable;
use Doctrine\DBAL\Driver\Connection;
Use Doctrine\ORM\EntityManagerInterface;
use OutOfBoundsException;
use Symfony\Component\Serializer\SerializerInterface;

class DocumentManager
{
    private $em;
    private $database;
    private $serializer;

    private $enabled = [
        'library',
        'service',
    ];

    public function __construct(EntityManagerInterface $entities, Connection $database, SerializerInterface $serializer)
    {
        $this->em = $entities;
        $this->database = $database;
        $this->serializer = $serializer;
    }

    public function getConnection() : Connection
    {
        return $this->database;
    }

    public function getEntityManager() : EntityManagerInterface
    {
        return $this->em;
    }

    public function write(ApiCacheable $entity) : void
    {
        try {
            $document = $this->serializer->normalize($entity, 'json', [
                'groups' => ['default', 'api_cache']
            ]);

            if (is_null($document)) {
                /**
                 * NULL would actually be serialized to a string "null" but we want a NULL.
                 *
                 * Intent is to allow the normalizer to return NULL for entities that should not
                 * be offered by the API, e.g. consortiums that are in fact Finna organisations.
                 */
                $serialized = null;
            } else {
                $serialized = $this->serializer->serialize($document, 'json', [
                    'groups' => ['default', 'api_cache']
                ]);
            }

            $query = $this->em->getRepository(get_class($entity))
                ->createQueryBuilder('e')
                ->update()
                ->where('e.id = :id')
                ->setParameter('id', $entity->getId())
                ;

            $query
                ->set('e.api_document', ':document')
                ->setParameter('document', $serialized)
                ;

            if ($entity->supportsApiKeywords()) {
                $entity->setApiDocument($document);
                $query
                    ->set('e.api_keywords', 'To_TsVector(\'simple\', :keywords)')
                    ->setParameter('keywords', implode(' ', $entity->getApiKeywords()))
                    ;
            }

            $query->getQuery()->execute();
        } catch (OutOfBoundsException $e) {
            // Unmanaged entity type.

            var_dump($e->getMessage());
            exit('fail');
        }
    }

    protected function serialize($entity) : array
    {
        $context = ['groups' => ['default', 'api_cache']];
        $document = $this->serializer->normalize($entity, 'json', $context);
        // $serialized = $this->serializer->serialize($entity, 'json', $context);

        $class_name = get_class($entity);
        $type_id = $this->types->getTypeId($class_name);

        return [$type_id, $document, null];
    }
}

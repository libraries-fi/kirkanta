<?php

namespace App\Module\ApiCache\EventListener;

use App\Entity\EntityDataBase;
use App\Module\ApiCache\DocumentManager;
use App\Module\ApiCache\Entity\Feature\ApiCacheable;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class CacheEntity
{
    private $manager;
    private $queue;

    public function __construct(DocumentManager $manager)
    {
        $this->manager = $manager;
    }

    public function postPersist(LifecycleEventArgs $event) : void
    {
        if ($entity = $this->getParentEntity($event->getEntity())) {
            if ($entity instanceof ApiCacheable) {
                $this->queue[] = $entity;
            }
        }
    }

    public function postUpdate(LifecycleEventArgs $event) : void
    {
        if ($entity = $this->getParentEntity($event->getEntity())) {
            if ($entity instanceof ApiCacheable) {
                $this->queue[] = $entity;
            }
        }
    }

    public function onKernelTerminate() : void
    {
        if (!$this->queue) {
            return;
        }

        $entities = array_unique($this->queue, SORT_REGULAR);
        $this->queue = [];

        foreach ($entities as $entity) {
            $this->manager->write($entity);
        }

        // Need to flush even though DocumentManager executes a DQL query.
        $this->manager->getEntityManager()->flush();
    }

    private function getParentEntity($entity)
    {
        /*
         * NOTE: Don't try to optimize by just checking if getFoo() exists and returning its value
         * straight; some entities might have many of the methods and only some of them might
         * return the parent.
         */

        if ($entity instanceof ApiCacheable) {
            return $entity;
        }

        if ($entity instanceof EntityDataBase) {
            return $this->getParentEntity($entity->getEntity());
        }

        if (method_exists($entity, 'getParent')) {
            if ($parent = $entity->getParent()) {
                return $parent;
            }
        }

        if (method_exists($entity, 'getLibrary')) {
            if ($parent = $entity->getLibrary()) {
                return $parent;
            }
        }

        if (method_exists($entity, 'getFinnaOrganisation')) {
            if ($parent = $entity->getFinnaOrganisation()) {
                return $parent;
            }
        }

        if (method_exists($entity, 'getConsortium')) {
            if ($parent = $entity->getConsortium()) {
                return $parent;
            }
        }
    }
}

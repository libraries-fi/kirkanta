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

    public function __construct(DocumentManager $manager)
    {
        $this->manager = $manager;
    }

    public function preUpdate(PreUpdateEventArgs $event) : void
    {
    }

    public function postUpdate(LifecycleEventArgs $event) : void
    {
        $entity = $event->getEntity();

        if ($entity instanceof EntityDataBase) {
            $entity = $entity->getEntity();
        }

        if ($entity instanceof ApiCacheable) {
            $this->manager->write($entity);
        }
    }
}

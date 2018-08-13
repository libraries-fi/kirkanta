<?php

namespace App\Doctrine\EventListener;

use DateTime;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use App\Entity\Feature\ModifiedAwareness;

class UpdateModifiedTime implements EventSubscriber
{
    public function getSubscribedEvents() : array
    {
        return [Events::prePersist, Events::preUpdate];
    }

    public function prePersist($args) : void
    {
        $this->preUpdate($args);
    }

    public function preUpdate(LifecycleEventArgs $args) : void
    {
        $entity = $args->getEntity();

        if ($entity instanceof ModifiedAwareness) {
            $entity->setModified(new DateTime);
        }
    }
}

<?php

namespace App\Doctrine\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use App\Doctrine\EntityRepository;

class AlterRepositoryClassSubscriber implements EventSubscriber
{
    public function getSubscribedEvents() : array
    {
        return [Events::loadClassMetadata];
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $args) : void
    {
        $metadata = $args->getClassMetadata();
        $custom_repo = $metadata->getMetadataValue('customRepositoryClassName');

        if (!$custom_repo && strpos($metadata->getName(), 'App\\') === 0) {
            $metadata->setCustomRepositoryClass(EntityRepository::class);
        }
    }
}

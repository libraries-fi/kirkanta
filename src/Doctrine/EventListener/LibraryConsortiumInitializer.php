<?php

namespace App\Doctrine\EventListener;

use App\Entity\Library;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

/**
 * Initializes municipal libraries with the consortium entity from their city.
 */
class LibraryConsortiumInitializer
{
    public function prePersist(Library $library, LifecycleEventArgs $args) : void
    {
        $this->initializeConsortium($library);
    }

    public function preUpdate(Library $library, LifecycleEventArgs $args) : void
    {
        $this->initializeConsortium($library);
    }

    private function initializeConsortium(Library $library) : void
    {
        if ($library->belongsToMunicipalConsortium()) {
            /**
             * Always set consortium. This way we can use this initializer to update
             * the consortium binding whenever the consortium for a city is changed.
             */
            $library->setConsortium($library->getCity()->getConsortium());
        } else {
            /**
             * NOTE: Do not unset consortium here.
             */
        }
    }
}

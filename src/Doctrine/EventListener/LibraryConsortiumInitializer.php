<?php

namespace App\Doctrine\EventListener;

use App\Entity\LibraryInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class LibraryConsortiumInitializer
{
    public function prePersist(LibraryInterface $library, LifecycleEventArgs $args) : void
    {
        $this->initializeConsortium($library);
    }

    public function preUpdate(LibraryInterface $library, LifecycleEventArgs $args) : void
    {
        $this->initializeConsortium($library);
    }

    private function initializeConsortium(LibraryInterface $library) : void
    {
        $allowed_types = ['municipal', 'main_library', 'music', 'regional', 'mobile'];

        if (in_array($library->getType(), $allowed_types)) {
            if (!$library->getConsortium()) {
                $library->setConsortium($library->getCity()->getConsortium());
            }
        } else {
            // Maybe the library's type was changed and it doesn't qualify for a consortium anymore.
            $library->setConsortium(null);
        }
    }
}

<?php

namespace App\Doctrine\EventListener;

use App\Entity\Library;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

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

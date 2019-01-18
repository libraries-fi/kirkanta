<?php

namespace App\Doctrine\EntityListener;

use App\Entity\LibraryPhoto;
use Doctrine\ORM\Event\LifecycleEventArgs;

class DefaultPictureListener
{
    public function prePersist(LibraryPhoto $photo, LifecycleEventArgs $args)
    {
        if ($photo->isDefaultPicture()) {
            $this->setDefaultPicture($photo);
        }
    }

    public function preUpdate(LibraryPhoto $photo, LifecycleEventArgs $args)
    {
        if ($photo->isDefaultPicture()) {
            $this->setDefaultPicture($photo);
        }
    }

    private function setDefaultPicture(LibraryPhoto $default_picture)
    {
        $pos = 1;

        foreach ($default_picture->getLibrary()->getPictures() as $picture) {
            $picture->setWeight($default_picture == $picture ? 0 : $pos++);
        }
    }
}

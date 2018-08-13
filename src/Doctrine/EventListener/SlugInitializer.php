<?php

namespace App\Doctrine\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use App\Entity\Feature\GroupOwnership;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SlugInitializer implements EventSubscriber
{
    public function __construct()
    {
    }

    public function getSubscribedEvents() : array
    {
        return [Events::prePersist];
    }

    public function prePersist(LifecycleEventArgs $args) : void
    {
        $entity = $args->getEntity();

        if ($entity instanceof Sluggable && !$entity->getSlug()) {

        }
    }

    public static function slugify($entity)
    {
        $regex = '/[^a-z]+/i';
        $methods = ['getName'];

        foreach ($methods as $method) {
            if (method_exists($entity, $method)) {
                $value = [$entity, $method]();
                $slug = preg_replace($regex, '-', $value) . '_' . rand(100, 999);
                $entity->setSlug($slug);
            }
        }
    }
}

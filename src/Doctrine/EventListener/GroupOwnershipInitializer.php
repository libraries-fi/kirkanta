<?php

namespace App\Doctrine\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use App\Entity\Feature\GroupOwnership;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class GroupOwnershipInitializer implements EventSubscriber
{
    private $authenticator;
    
    public function __construct(TokenStorageInterface $authenticator)
    {
        $this->authenticator = $authenticator;
    }

    public function getSubscribedEvents() : array
    {
        return [Events::prePersist];
    }

    public function prePersist(LifecycleEventArgs $args) : void
    {
        $entity = $args->getEntity();

        if ($entity instanceof GroupOwnership && !$entity->hasOwner()) {
            $user = $this->authenticator->getToken()->getUser();
            $entity->setOwner($user->getGroup());
        }
    }
}

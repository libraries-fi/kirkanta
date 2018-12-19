<?php

namespace App\Doctrine\EntityListener;

use App\Entity\Consortium;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ConsortiumGroupOwnershipInitializer
{
    private $authenticator;

    public function __construct(TokenStorageInterface $authenticator)
    {
        $this->authenticator = $authenticator;
    }

    public function prePersist(Consortium $consortium, LifecycleEventArgs $args) : void
    {
        $user = $this->authenticator->getToken()->getUser();
        $groups = $user->getGroup()->getTree();
        $owner = end($groups);

        $consortium->setOwner($owner);


        // exit('set group owner');
    }
}

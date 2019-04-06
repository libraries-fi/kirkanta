<?php

namespace App\Doctrine;

use App\Entity\Notification;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserRepository extends EntityRepository implements UserLoaderInterface
{
    public function loadUserByUsername($username) : ?UserInterface
    {
        return $this->createQueryBuilder('u')
            ->orWhere('u.username = :login')
            ->orWhere('u.email = :login')
            ->setParameter('login', $username)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }
}

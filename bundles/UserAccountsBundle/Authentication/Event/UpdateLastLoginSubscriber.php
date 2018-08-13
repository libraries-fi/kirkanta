<?php

namespace UserAccountsBundle\Authentication\Event;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class UpdateLastLoginSubscriber implements EventSubscriberInterface
{
    private $entity_manager;
    private $user_class;

    public static function getSubscribedEvents()
    {
        return ['security.interactive_login' => ['onLogin']];
    }

    public function __construct(EntityManagerInterface $entity_manager, string $user_class)
    {
        $this->entity_manager = $entity_manager;
        $this->user_class = $user_class;
    }

    public function onLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        $qb = $this->entity_manager->createQueryBuilder();
        $qb->update($this->user_class, 'u')
        ->set('u.last_login', $qb->expr()->literal((new DateTime)->format('Y-m-d H:i:s')))
        ->where('u.id = ?1')
        ->setParameter(1, $user->getId())
        ->getQuery()
        ->execute();
    }
}

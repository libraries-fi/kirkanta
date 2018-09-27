<?php

namespace App\EventListener;

use App\EntityTypeManager;
use App\Entity\Feature\GroupOwnership;
use DomainException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Handles scaling uploaded images to given sizes.
 */
class EntityAccessGuard implements EventSubscriberInterface
{
    private $types;
    private $auth;

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => ['onController', 1000]
        ];
    }

    public function __construct(EntityTypeManager $types, AuthorizationCheckerInterface $auth)
    {
        $this->types = $types;
        $this->auth = $auth;
    }

    public function onController(FilterControllerEvent $event) : void
    {
        $params = $event->getRequest()->attributes;

        if (preg_match('/^entity.(\w+).(\w+)$/', $params->get('_route'), $match)) {
            list($_, $entity_type, $action) = $match;
            $entity_class = $this->types->getEntityClass($entity_type);

            if ($params->has($entity_type) && is_a($entity_class, GroupOwnership::class, true)) {
                $entity = $this->types->getEntityManager()->find($entity_class, $params->get($entity_type));

                if (!$this->auth->isGranted($action, $entity)) {
                    throw new AccessDeniedHttpException('You are not allowed to access this entity.');
                }
            }
        }
    }
}

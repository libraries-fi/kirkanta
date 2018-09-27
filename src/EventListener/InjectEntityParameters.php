<?php

namespace App\EventListener;

use App\EntityTypeManager;
use App\Entity\Feature\GroupOwnership;
use DomainException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Handles scaling uploaded images to given sizes.
 */
class InjectEntityParameters implements EventSubscriberInterface
{
    private $types;

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['preView', 1000]
        ];
    }

    public function __construct(EntityTypeManager $types)
    {
        $this->types = $types;
    }

    public function preView(GetResponseForControllerResultEvent $event) : void
    {
        $attributes = $event->getRequest()->attributes;
        $route_name = $attributes->get('_route');
        list($prefix, $entity_type) = explode('.', $route_name . '.');

        if ($prefix == 'entity') {
            $values = $event->getControllerResult();

            if (empty($values['entity_type'])) {
                $values['type_label'] = $this->types->getTypeLabel($entity_type);
                $values['entity_type'] = $entity_type;
                $values[$entity_type] = $attributes->get($entity_type);
                $event->setControllerResult($values);
            }
        }
    }
}

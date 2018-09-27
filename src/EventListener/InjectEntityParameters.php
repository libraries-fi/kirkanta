<?php

namespace App\EventListener;

use App\EntityTypeManager;
use App\Entity\Feature\GroupOwnership;
use App\Util\LibraryResources;
use DomainException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Injects common view variables used by entity templates.
 */
class InjectEntityParameters implements EventSubscriberInterface
{
    private $types;
    private $libraryResources;

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['preView', 1000]
        ];
    }

    public function __construct(EntityTypeManager $types, LibraryResources $resources)
    {
        $this->types = $types;
        $this->libraryResources = $resources;
    }

    public function preView(GetResponseForControllerResultEvent $event) : void
    {
        $attributes = $event->getRequest()->attributes;
        $route_name = $attributes->get('_route');
        list($prefix, $entity_type, $resource, $action) = explode('.', $route_name . '...');

        if ($prefix == 'entity') {
            $values = $event->getControllerResult();

            if ($type = $this->libraryResources[$resource]) {
                $entity_type = $type;
            }

            if (!$action) {
                $action = $resource ?: 'collection';
            }

            if (empty($values['entity_type'])) {
                $values['type_label'] = $this->types->getTypeLabel($entity_type, $action == 'collection');
                $values['entity_type'] = $entity_type;

                if ($entity = $attributes->get($entity_type)) {
                    $values[$entity_type] = $entity;
                }

                $event->setControllerResult($values);
            }
        }
    }
}

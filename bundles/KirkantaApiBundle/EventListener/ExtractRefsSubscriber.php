<?php

namespace KirjastotFi\KirkantaApiBundle\EventListener;

use stdClass;
use FOS\RestBundle\View\View;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

use Symfony\Component\PropertyAccess\PropertyAccess;

use App\Entity\Organisation;
use App\Entity\Feature\StateAwareness;

class ExtractRefsSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents() : array
    {
        return [
            // Execute before FOSRestBundle:ViewResponseListener
            KernelEvents::VIEW => ['onKernelView', 40]
        ];
    }

    public function onKernelView(GetResponseForControllerResultEvent $event) : void
    {
        $result = $event->getControllerResult();
        $refs = $result instanceof View ? $result->getContext()->getAttribute('refs') : [];

        if ($refs) {
            $data = $result->getData();
            $data['refs'] = $this->collectRefs($data['result'], $refs);
            $result->setData($data);
        }
    }

    protected function collectRefs(iterable $items, array $refs) : array
    {
        $map = [
            Organisation::class => [
                'city' => 'city',
                'consortium' => 'consortium'
            ]
        ];

        $accessor = PropertyAccess::createPropertyAccessor();
        $references = [];

        foreach ($refs as $property) {
            foreach ($items as $entity) {
                if ($accessor->isReadable($entity, $property)) {
                    if ($entity = $accessor->getValue($entity, $property)) {
                        if (!($entity instanceof StateAwareness) || $entity->isPublished()) {
                            $key = $entity->getId();
                            $references[$property][(string)$key] = $entity;
                        }
                    }
                }
            }
        }

        return $references;
    }
}

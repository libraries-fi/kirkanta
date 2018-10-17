<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Redirects forms after successful submit.
 */
class UpdateFormResponseStatusSubscriber implements EventSubscriberInterface
{
    private $types;
    private $loader;
    private $libraryResources;

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['onKernelView', 999],
            KernelEvents::RESPONSE => ['onKernelResponse', 999],
        ];
    }

    public function onKernelView(GetResponseForControllerResultEvent $event) : void
    {
        $result = $event->getControllerResult();

        if (is_array($result) && isset($result['form']) && !$result['form']->vars['valid']) {
            $event->getRequest()->attributes->set('_form_invalid', true);
        }
    }

    public function onKernelResponse(FilterResponseEvent $event) : void
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        if ($response->getStatusCode() == 200 && $request->attributes->get('_form_invalid')) {
            $response->setStatusCode(422);
        }
    }
}

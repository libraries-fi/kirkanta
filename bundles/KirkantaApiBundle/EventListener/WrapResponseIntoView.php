<?php

namespace KirjastotFi\KirkantaApiBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class WrapResponseIntoView implements EventSubscriberInterface
{
    public static function getSubscribedEvents() : array
    {
        return [
            /*
             * FOSRestBundle::ViewResponseListener has priority 30.
             * Our listener must run first.
             */
            KernelEvents::VIEW => ['onKernelView', 31]
        ];
    }

    public function onKernelView(GetResponseForControllerResultEvent $event) : void
    {
        // $api_prefix =
        // var_dump($event->getRequest()->getPathInfo());
        // var_dump($event->getController());
        // exit;
    }
}

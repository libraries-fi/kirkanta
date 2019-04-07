<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Redirects forms after successful submit.
 */
class LibraryResourceRedirectSubscriber implements EventSubscriberInterface
{
    private $urls;

    public function __construct(UrlGeneratorInterface $urls)
    {
        $this->urls = $urls;
    }

    public static function getSubscribedEvents()
    {
        return [
            // NOTE: Run after EntityTemplateResolver!
            KernelEvents::RESPONSE => ['onKernelResponse', 900]
        ];
    }

    public function onKernelResponse(FilterResponseEvent $event) : void
    {
        $request = $event->getRequest();
        $response = $event->getResponse();
        $route_name = $request->attributes->get('_route');

        if ($request->isMethod('POST') && $response->getStatusCode() == 302) {
            if (preg_match('/^entity\.(library|service_point)\.(\w+)\.translate$/', $route_name, $match)) {
                $target_route = str_replace('.translate', '.edit', $route_name);

                $params = array_intersect_key($request->attributes->all(), array_flip([
                    'entity_type',
                    'resource_id',
                ]));

                $entity_type = $params['entity_type'];
                $params[$entity_type] = $request->attributes->get($entity_type)->getId();

                parse_str(parse_url($response->getTargetUrl(), PHP_URL_QUERY), $query);

                $url = $this->urls->generate($target_route, $params + $query);
                $response->setTargetUrl($url);
            } else if (preg_match('/^entity\.(library|service_point)\.(\w+)\.(\w+)$/', $route_name, $match)) {
                $target_route = "entity.{$match[1]}.{$match[2]}";
                $entity_id = $request->attributes->get($match[1]);

                if (is_object($entity_id)) {
                    $entity_id = $entity_id->getId();
                }

                $url = $this->urls->generate($target_route, [
                    $match[1] => $entity_id
                ]);

                $response->setTargetUrl($url);
            }
        }
    }

    private function matchRouteName(string $route_name) : ?array
    {
        if (preg_match('/^entity\.(library|service_point)\.(\w+)\.(\w+)$/', $route_name, $match)) {
            return $match;
        }

        // if (preg_match)
    }
}

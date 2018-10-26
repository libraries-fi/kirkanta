<?php

namespace App\EventListener;

use App\EntityTypeManager;
use App\Entity\Feature\GroupOwnership;
use App\Util\LibraryResources;
use DomainException;
use Twig_Environment as Twig;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Handles scaling uploaded images to given sizes.
 */
class LibraryTemplateResolver implements EventSubscriberInterface
{
    private $urlMatcher;
    private $loader;
    private $libraryResources;

    public static function getSubscribedEvents()
    {
        return [
            // NOTE: Run after EntityTemplateResolver!
            KernelEvents::VIEW => ['preView', 999]
        ];
    }

    public function __construct(UrlMatcherInterface $url_matcher, Twig $twig, LibraryResources $resources)
    {
        $this->urlMatcher = $url_matcher;
        $this->loader = $twig->getLoader();
        $this->libraryResources = $resources;
    }

    public function preView(GetResponseForControllerResultEvent $event) : void
    {
        $attributes = $event->getRequest()->attributes;
        $route_name = $attributes->get('_route');

        if (!$attributes->get('_template')) {
            if (!$route_name) {
                // Probably processing a forwarded request.

                $match = $this->urlMatcher->match($event->getRequest()->getPathInfo());
                $route_name = $match['_route'];
            }

            list($prefix, $entity_type, $resource, $action) = explode('.', $route_name . '...');

            if (in_array($entity_type, ['library', 'service_point'])) {
                if ($type = $this->libraryResources[$resource]) {
                    $action = $action ?: 'collection';
                } else {
                    $action = 'collection';
                }

                if ($template = $this->resolveTemplate($action, $resource)) {
                    $object = new Template(['template' => $template]);
                    $attributes->set('_template', $object);
                }
            }
        }
    }

    private function resolveTemplate(string $action, string $resource_type) : ?string
    {
        $resource_type = str_replace('_', '-', $resource_type);
        $names = [
            sprintf('entity/Library/%s.%s.html.twig', $resource_type, $action),
            sprintf('entity/Library/resource.%s.html.twig', $action),
        ];

        foreach ($names as $name) {
            if ($this->loader->exists($name)) {
                return $name;
            }
        }

        if ($action == 'add') {
            return $this->resolveTemplate('edit', $resource_type);
        } else {
            return null;
        }
    }
}

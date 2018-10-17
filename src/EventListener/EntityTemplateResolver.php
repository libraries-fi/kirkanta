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
class EntityTemplateResolver implements EventSubscriberInterface
{
    private $types;
    private $loader;
    private $urlMatcher;

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['preView', 1000]
        ];
    }

    public function __construct(EntityTypeManager $types, Twig $twig, UrlMatcherInterface $url_matcher)
    {
        $this->types = $types;
        $this->loader = $twig->getLoader();
        $this->urlMatcher = $url_matcher;
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

            list($prefix, $entity_type, $action) = explode('.', $route_name . '..');

            if ($prefix == 'entity' && $action) {
                if ($template = $this->resolveTemplate($action, $entity_type)) {
                    $object = new Template(['template' => $template]);
                    $attributes->set('_template', $object);
                }
            }
        }
    }

    private function resolveTemplate(string $action, string $entity_type) : ?string
    {
        $entity_class = $this->types->getEntityClass($entity_type);
        $class_name = substr(strrchr($entity_class, '\\'), 1);

        $names = [
            sprintf('@ServiceTree/entity/%s/%s.html.twig', $class_name, $action),
            sprintf('entity/%s/%s.html.twig', $class_name, $action),
            sprintf('entity/%s.html.twig', $action),
        ];

        foreach ($names as $name) {
            if ($this->loader->exists($name)) {
                return $name;
            }
        }

        if ($action == 'add') {
            return $this->resolveTemplate('edit', $entity_type);
        } else {
            return null;
        }
    }
}

<?php

namespace App\Menu;

use App\Controller\OrganisationController;
use App\EntityTypeManager;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class BreadcrumbBuilder
{
    private $factory;
    private $types;
    private $matcher;

    public function __construct(FactoryInterface $factory, EntityTypeManager $types, UrlMatcherInterface $matcher)
    {
        $this->factory = $factory;
        $this->types = $types;
        $this->matcher = $matcher;
    }

    public function build(RequestStack $request_stack) : ItemInterface
    {
        $menu = $this->factory->createItem('root');
        $menu->addChild('Kirkanta', [
            'route' => 'front'
        ]);

        $current_path = $request_stack->getCurrentRequest()->getPathInfo();

        while (($i = strpos($current_path, '/', ($i ?? 0) + 1)) !== FALSE) {
            try {
                $path = substr($current_path, 0, $i);
                $match = $this->matcher->match($path);
                $title = $this->resolveRouteTitle($match['_route'], $match);

                if ($title) {
                    $menu->addChild($title, [
                        'route' => $match['_route'],
                        'routeParameters' => array_filter($match, function($v, $k) { return $k[0] != '_'; }, ARRAY_FILTER_USE_BOTH),
                    ]);
                }
            } catch (ResourceNotFoundException $e) {
                // Thrown when there's no route for given path. Pass.
            }
        }

        return $menu;
    }

    private function resolveRouteTitle(string $route_name, array $parameters) : ?string
    {
        if (preg_match('/^entity\.\w+\.(collection|edit|delete)$/', $route_name, $match)) {
            list($_, $action) = $match;
            $type_id = $parameters['entity_type'];

            switch ($action) {
                case 'collection':
                    return $this->types->getTypeLabel($type_id, true);

                case 'edit':
                    $id = $parameters[$type_id];
                    $entity = $this->types->getRepository($type_id)->findOneById($id);

                    $methods = ['getName', 'getTitle'];
                    foreach ($methods as $method) {
                        if (method_exists($entity, $method)) {
                            return call_user_func([$entity, $method]);
                        }
                    }
                    return '#' . $entity->getId();
            }
        } elseif ($route_name == 'entity.library.resource_collection') {
            $resource = $parameters['resource'];
            $type_id = OrganisationController::$resources[$resource];
            return $this->types->getTypeLabel($type_id, true);
        }

        $titles = [
            'admin' => 'Administration',
            'user_management.own_group' => 'User management',
        ];

        return $titles[$route_name] ?? null;
    }
}

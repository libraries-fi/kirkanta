<?php

namespace App\Routing;

use RuntimeException;
use App\EntityTypeManager;
use App\Controller\EntityController;
use App\Entity\Feature\StateAwareness;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class EntityRouteLoader extends Loader
{
    private $loaded = false;
    private $types;

    public function __construct(EntityTypeManager $types)
    {
        $this->types = $types;
    }

    public function supports($resource, $type = null) : bool
    {
        return $type == 'entity_routes';
    }

    public function load($resource, $type = null)
    {
        if ($this->loaded) {
            throw new RuntimeException('Trying to load entity routes again');
        }

        $types = $this->types->getTypes();
        $routes = new RouteCollection;

        foreach ($types as $type_id => $definition) {
            $base_path = "/{$type_id}";
            $defaults = [
                'entity_type' => $type_id
            ];

            $collection = new Route($base_path, $defaults + [
                '_controller' => sprintf('%s:collection', EntityController::class),
            ]);

            $add = new Route("{$base_path}/add", $defaults + [
                '_controller' => sprintf('%s:add', EntityController::class)
            ]);

            $edit = new Route("{$base_path}/{{$type_id}}", $defaults + [
                '_controller' => sprintf('%s:edit', EntityController::class)
            ], [
                $type_id => '\d+'
            ]);

            $delete = new Route("{$base_path}/{{$type_id}}/delete", $defaults + [
                '_controller' => sprintf('%s:delete', EntityController::class)
            ], [
                $type_id => '\d+'
            ]);

            $translate = new Route("{$base_path}/{{$type_id}}/translate", $defaults + [
                '_controller' => sprintf('%s:translate', EntityController::class)
            ], [
                $type_id => '\d+'
            ]);

            if (is_a($definition['class_name'], StateAwareness::class, true)) {
                $recycled = new Route("{$base_path}/recycled", $defaults + [
                    '_controller' => sprintf('%s::collection', EntityController::class),
                    'recycled' => true,
                ]);

                $restore = new Route("{$base_path}/{{$type_id}}/restore", $defaults + [
                    '_controller' => sprintf('%s::restore', EntityController::class),
                ]);

                $routes->add("entity.{$type_id}.recycled", $recycled);
                $routes->add("entity.{$type_id}.restore", $restore);
            }

            $routes->add("entity.{$type_id}.collection", $collection);
            $routes->add("entity.{$type_id}.add", $add);
            $routes->add("entity.{$type_id}.edit", $edit);
            $routes->add("entity.{$type_id}.delete", $delete);
            $routes->add("entity.{$type_id}.translate", $translate);
        }

        $this->loaded = true;

        return $routes;
    }
}

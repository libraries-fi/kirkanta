<?php

namespace App\Module\Finna;

use RuntimeException;
use App\EntityTypeManager;
use App\Module\Finna\Controller\FinnaController;
use App\Entity\Feature\StateAwareness;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RouteLoader extends Loader
{
    private $loaded = false;
    private $types;

    public function __construct(EntityTypeManager $types)
    {
        $this->types = $types;
    }

    public function supports($resource, $type = null) : bool
    {
        return $type == 'finna_routes';
    }

    public function load($resource, $type = null) : RouteCollection
    {
        if ($this->loaded) {
            throw new RuntimeException('Trying to load consortium routes again');
        }

        $this->loaded = true;

        $routes = new RouteCollection();
        // $resources = ['links', 'link_groups'];
        // $base_path = '/finna_organisation/{finna_organisation}';
        //
        // $defaults = [
        //     'entity_type' => 'finna_organisation',
        // ];

        // $requirements = [
        //     'finna_organisation' => '\d+',
        //     'resource' => implode('|', $resources),
        // ];

        // $resource_collection = new Route("{$base_path}/{resource}", $defaults + [
        //     '_controller' => sprintf('%s:resourceCollection', FinnaController::class)
        // ], $requirements);
        //
        // $add_resource = new Route("{$base_path}/{resource}/add", $defaults + [
        //     '_controller' => sprintf('%s:addResource', FinnaController::class)
        // ], $requirements);
        //
        // $edit_resource = new Route("{$base_path}/{resource}/{resource_id}/edit", $defaults + [
        //     '_controller' => sprintf('%s:editResource', FinnaController::class)
        // ], $requirements + [
        //     'resource_id' => '\d+'
        // ]);
        //
        // $delete_resource = new Route("{$base_path}/{resource}/{resource_id}/delete", $defaults + [
        //     '_controller' => sprintf('%s:editResource', FinnaController::class)
        // ], $requirements + [
        //     'resource_id' => '\d+'
        // ]);

        // $table_sort = new Route("{$base_path}/{resource}/tablesort", $defaults + [
        //     '_controller' => sprintf('%s:tableSort', FinnaController::class)
        // ], $requirements);

        // $routes->add('entity.finna_organisation.resource_collection', $resource_collection);
        // $routes->add('entity.finna_organisation.add_resource', $add_resource);
        // $routes->add('entity.finna_organisation.edit_resource', $edit_resource);
        // $routes->add('entity.finna_organisation.delete_resource', $delete_resource);
        // $routes->add('entity.finna_organisation.table_sort', $table_sort);

        return $routes;
    }
}

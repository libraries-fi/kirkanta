<?php

namespace App\Routing;

use RuntimeException;
use App\EntityTypeManager;
use App\Controller\OrganisationController;
use App\Entity\Feature\StateAwareness;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class LibraryRouteLoader extends Loader
{
    private $loaded = false;
    private $types;

    public function __construct(EntityTypeManager $types)
    {
        $this->types = $types;
    }

    public function supports($resource, $type = null) : bool
    {
        return $type == 'library_routes';
    }

    public function load($resource, $type = null) : RouteCollection
    {
        if ($this->loaded) {
            throw new RuntimeException('Trying to load library routes again');
        }

        $this->loaded = true;

        $routes = new RouteCollection;
        $derivatives = ['library', 'service_point'];
        $resources = [
            'departments',
            'email_addresses',
            'links',
            'periods',
            'persons',
            'phone_numbers',
            'pictures',
            'services'
        ];

        foreach ($derivatives as $type_id) {
            $defaults = ['entity_type' => $type_id];
            $requirements = [$type_id => '\d+'];

            foreach ($resources as $resource) {
                $base_path = "/{$type_id}/{{$type_id}}";

                if (in_array($resource, ['email_addresses', 'links', 'phone_numbers'])) {
                    $base_path .= '/contact';
                }

                $resource_collection = new Route("{$base_path}/{$resource}", $defaults + [
                    'resource' => $resource,
                    '_controller' => sprintf('%s:resourceCollection', OrganisationController::class)
                ], $requirements);

                $add_resource = new Route("{$base_path}/{$resource}/add", $defaults + [
                    'resource' => $resource,
                    '_controller' => sprintf('%s:addResource', OrganisationController::class)
                ], $requirements);

                $edit_resource = new Route("{$base_path}/{$resource}/{resource_id}/edit", $defaults + [
                    'resource' => $resource,
                    '_controller' => sprintf('%s:editResource', OrganisationController::class)
                ], $requirements + [
                    'resource_id' => '\d+'
                ]);

                $delete_resource = new Route("{$base_path}/{$resource}/{resource_id}/delete", $defaults + [
                    'resource' => $resource,
                    '_controller' => sprintf('%s:editResource', OrganisationController::class)
                ], $requirements + [
                    'resource_id' => '\d+'
                ]);

                $table_sort = new Route("{$base_path}/{$resource}/tablesort", $defaults + [
                    'resource' => $resource,
                    '_controller' => sprintf('%s:tableSort', OrganisationController::class)
                ], $requirements);

                $routes->add("entity.{$type_id}.{$resource}", $resource_collection);
                $routes->add("entity.{$type_id}.{$resource}.add", $add_resource);
                $routes->add("entity.{$type_id}.{$resource}.edit", $edit_resource);
                $routes->add("entity.{$type_id}.{$resource}.delete", $delete_resource);
                $routes->add("entity.{$type_id}.{$resource}.table_sort", $table_sort);
            }

            $contact_info = new Route("{$base_path}/contact", $defaults + [
                '_controller' => sprintf('%s:contactsTab', OrganisationController::class)
            ]);

            $routes->add("entity.{$type_id}.contact_info", $contact_info);
        }

        return $routes;
    }
}

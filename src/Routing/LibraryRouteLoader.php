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
                    '_controller' => sprintf('%s:deleteResource', OrganisationController::class)
                ], $requirements + [
                    'resource_id' => '\d+'
                ]);

                $table_sort = new Route("{$base_path}/{$resource}/tablesort", $defaults + [
                    'resource' => $resource,
                    '_controller' => sprintf('%s:tableSort', OrganisationController::class)
                ], $requirements);

                $from_template = new Route("{$base_path}/{$resource}/import", $defaults + [
                    'resource' => $resource,
                    '_controller' => sprintf('%s::resourceFromTemplate', OrganisationController::class)
                ], $requirements);

                $routes->add("entity.{$type_id}.{$resource}", $resource_collection);
                $routes->add("entity.{$type_id}.{$resource}.add", $add_resource);
                $routes->add("entity.{$type_id}.{$resource}.edit", $edit_resource);
                $routes->add("entity.{$type_id}.{$resource}.delete", $delete_resource);
                $routes->add("entity.{$type_id}.{$resource}.table_sort", $table_sort);
                $routes->add("entity.{$type_id}.{$resource}.from_template", $from_template);
            }

            $contact_group_collection = new Route("/{$type_id}/{{$type_id}}/contact", $defaults + [
                'resource' => 'contact_groups',
                '_controller' => sprintf('%s::resourceCollection', OrganisationController::class)
            ]);

            $contact_group_add = new Route("/{$type_id}/{{$type_id}}/contact/add", $defaults + [
                'resource' => 'contact_groups',
                '_controller' => sprintf('%s::addResource', OrganisationController::class)
            ]);

            $contact_group_edit = new Route("/{$type_id}/{{$type_id}}/contact/{resource_id}", $defaults + [
                'resource' => 'contact_groups',
                '_controller' => sprintf('%s::editResource', OrganisationController::class)
            ], $requirements + [
                'resource_id' => '\d+'
            ]);

            $contact_group_delete = new Route("/{$type_id}/{{$type_id}}/contact/{resource_id}/delete", $defaults + [
                'resource' => 'contact_groups',
                '_controller' => sprintf('%s::deleteResource', OrganisationController::class)
            ], $requirements + [
                'resource_id' => '\d+'
            ]);

            $routes->add("entity.{$type_id}.contact_groups", $contact_group_collection);
            $routes->add("entity.{$type_id}.contact_groups.add", $contact_group_add);
            $routes->add("entity.{$type_id}.contact_groups.edit", $contact_group_edit);
            $routes->add("entity.{$type_id}.contact_groups.delete", $contact_group_delete);

            $custom_data_collection = new Route("/{$type_id}/{{$type_id}}/custom-data", $defaults + [
                '_controller' => sprintf('%s::listCustomData', OrganisationController::class)
            ]);

            $custom_data_add = new Route("/{$type_id}/{{$type_id}}/custom-data/add", $defaults + [
                '_controller' => sprintf('%s::addCustomData', OrganisationController::class)
            ]);

            $custom_data_edit = new Route("/{$type_id}/{{$type_id}}/custom-data/{custom_data}", $defaults + [
                '_controller' => sprintf('%s::editCustomData', OrganisationController::class)
            ], $requirements + [
                'custom_data' => '\d+'
            ]);

            $custom_data_delete = new Route("/{$type_id}/{{$type_id}}/custom-data/{custom_data}/delete", $defaults + [
                '_controller' => sprintf('%s::deleteCustomData', OrganisationController::class)
            ], $requirements + [
                'custom_data' => '\d+'
            ]);

            $routes->add("entity.{$type_id}.custom_data", $custom_data_collection);
            $routes->add("entity.{$type_id}.custom_data.add", $custom_data_add);
            $routes->add("entity.{$type_id}.custom_data.edit", $custom_data_edit);
            $routes->add("entity.{$type_id}.custom_data.delete", $custom_data_delete);
        }

        return $routes;
    }
}

<?php

namespace App\Menu\Breadcrumb;

use App\EntityTypeManager;

class EntityCrumbProvider extends CommonBreadcrumbProvider
{
    private $types;

    public function __construct(EntityTypeManager $types)
    {
        $this->types = $types;
    }

    public function supports(string $route_name, array $params = []) : bool
    {
        return preg_match('/^entity\.\w+\.(collection|edit|delete)$/', $route_name);
    }

    protected function getItemLabel(string $route_name, array $params) : ?array
    {
        preg_match('/^entity\.\w+\.(collection|edit|delete)$/', $route_name, $match);
        list($_, $action) = $match;

        $type_id = $params['entity_type'];

        switch ($action) {
            case 'collection':
                return [$this->types->getTypeLabel($type_id, true)];

            case 'edit':
                $id = $params[$type_id];
                $entity = $this->types->getRepository($type_id)->findOneById($id);

                $methods = ['getName', 'getTitle'];
                foreach ($methods as $method) {
                    if (method_exists($entity, $method)) {
                        return [call_user_func([$entity, $method]), false];
                    }
                }
                return ['#' . $entity->getId(), false];
        }
    }
}

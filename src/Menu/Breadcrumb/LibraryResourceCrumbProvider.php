<?php

namespace App\Menu\Breadcrumb;

use App\EntityTypeManager;
use App\Util\LibraryResources;

class LibraryResourceCrumbProvider extends CommonBreadcrumbProvider
{
    private $types;
    private $resources;

    public function __construct(EntityTypeManager $types, LibraryResources $resources)
    {
        $this->types = $types;
        $this->resources = $resources;
    }

    public function supports(string $route_name, array $params = []) : bool
    {
        return $route_name == 'entity.library.resource_collection';
    }

    protected function getItemLabel(string $route_name, array $params) : ?array
    {
        $type_id = $this->resources->offsetGet($params['resource']);
        return $this->types->getTypeLabel($type_id, true);
    }
}

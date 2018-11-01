<?php

namespace App\Menu\Breadcrumb;

interface BreadcrumbProviderInterface
{
    public function supports(string $route_name, array $params = []) : bool;
    public function getMenuItem(string $route_name, array $params = []) : ?array;
}

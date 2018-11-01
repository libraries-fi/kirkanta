<?php

namespace App\Menu\Breadcrumb;

use App\EntityTypeManager;
use Knp\Menu\ItemInterface;

abstract class CommonBreadcrumbProvider implements BreadcrumbProviderInterface
{
    abstract protected function getItemLabel(string $route_name, array $params) : ?array;

    public function getMenuItem(string $route_name, array $params = []) : ?array
    {
        if ($label_options = $this->getItemLabel($route_name, $params)) {
            list($label, $translation_domain) = $label_options + [1 => 'messages'];

            return [
                'label' => $label,
                'options' => [
                    'route' => $route_name,
                    'routeParameters' => $params,
                    'extras' => [
                        'translation_domain' => $translation_domain
                    ]
                ]
            ];
        }
    }
}

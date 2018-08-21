<?php

namespace App\Menu;

use App\EntityTypeManager;
use Knp\Menu\Factory\ExtensionInterface;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\RequestStack;

class Builder implements ContainerAwareInterface, ExtensionInterface
{
    use ContainerAwareTrait;

    private $factory;
    private $typeManager;
    private $auth;

    public function __construct(FactoryInterface $factory, EntityTypeManager $type_manager, AuthorizationCheckerInterface $auth)
    {
        $this->factory = $factory;
        $this->typeManager = $type_manager;
        $this->auth = $auth;

        $this->factory->addExtension($this, -10);
    }

    protected function filterMenuItems(ItemInterface $menu) : ItemInterface
    {
        foreach ($menu->getChildren() as $item) {
            if (($access = $item->getExtra('access')) !== null) {
                if (!array_filter($access)) {
                    $menu->removeChild($item->getName());
                }
            }
        }

        return $menu;
    }

    public function mainMenu(RequestStack $request_stack) : ItemInterface
    {
        $menu = $this->factory->createItem('root');
        $request = $request_stack->getCurrentRequest();
        $current_type_id = $request->attributes->get('type');

        $types = [
            'library' => null,
            // 'consortium' => null,
            // 'finna_organisation' => null,
            'person' => 'Staff',
            'service_instance' => 'Service templates',
            'period' => 'Period templates'
        ];

        foreach ($types as $type_id => $label) {
            if (!$label) {
                $label = $this->typeManager->getTypeLabel($type_id, true);
            }

            $item = $menu->addChild($label, [
                'route' => "entity.{$type_id}.collection",
            ]);

            if ($type_id == $current_type_id) {
                $item->addChild('Add', [
                    'route' => "entity.{$type_id}.add",
                ]);

                if ($id = $request->attributes->get('id')) {
                    $item->addChild('Edit', [
                        'route' => "entity.{$type_id}.edit",
                        'routeParameters' => [
                            $type_id => $id
                        ]
                    ]);

                    $item->addChild('Delete', [
                        'route' => "entity.{$type_id}.delete",
                        'routeParameters' => [
                            $type_id => $id
                        ]
                    ]);

                    if ($current_type_id == 'organisation') {
                        $submenu = $this->organisationTabs($request_stack);

                        foreach ($submenu->getChildren() as $child) {
                            $item->addChild($child->copy());
                        }
                    }
                }
            }
        }

        if ($this->auth->isGranted('ROLE_ROOT')) {
            $menu->addChild('Admin', [
                'route' => 'admin',
            ]);
        }

        return $this->filterMenuItems($menu);
    }

    public function libraryTabs(RequestStack $request_stack) : ItemInterface
    {
        $request = $request_stack->getCurrentRequest();
        $library = $request->attributes->get('library');
        $menu = $this->factory->createItem('root');

        if (!is_object($library)) {
            $library = $this->typeManager->getRepository('library')->findOneById($library);
        }

        $resources = [
            'periods' => 'Periods',
            'services' => 'Services',
            'pictures' => 'Pictures',
            'persons' => 'Staff',
            'departments' => 'Departments',
            'phone_numbers' => 'Phone Numbers',
        ];

        $menu->addChild('Basic details', [
            'route' => 'entity.library.edit',
            'routeParameters' => [
                'library' => $library->getId(),
            ]
        ]);

        foreach ($resources as $resource => $label) {
            $item = $menu->addChild($label, [
                'route' => 'entity.library.resource_collection',
                'routeParameters' => [
                    'library' => $library->getId(),
                    'resource' => $resource,
                ]
            ]);

            if ($resource == $request->get('resource')) {
                $item->addChild('Add', [
                    'route' => 'entity.library.add_resource',
                    'routeParameters' => [
                        'library' => $library->getId(),
                        'resource' => $resource,
                    ]
                ]);

                if ($rid = $request->get('resource_id')) {
                    $item->addChild('Edit', [
                        'route' => 'entity.library.edit_resource',
                        'routeParameters' => [
                            'library' => $library->getId(),
                            'resource' => $resource,
                            'resource_id' => $rid
                        ]
                    ]);
                    $item->addChild('Delete', [
                        'route' => 'entity.library.delete_resource',
                        'routeParameters' => [
                            'library' => $library->getId(),
                            'resource' => $resource,
                            'resource_id' => $rid
                        ]
                    ]);
                }
            }
        }

        return $menu;
    }

    public function adminMenu() : ItemInterface
    {
        $entities = ['consortium', 'finna_organisation', 'city', 'region', 'regional_library', 'service', 'user', 'user_group'];
        $menu = $this->factory->createItem('root');

        foreach ($entities as $type_id) {
            $menu->addChild($this->typeManager->getTypeLabel($type_id, true), [
                'route' => sprintf('entity.%s.collection', $type_id),
            ]);
        }

        return $menu;
    }

    public function adminTools() : ItemInterface
    {
        $menu = $this->factory->createItem('root');
        $menu->addChild('Translation', [
            'route' => 'translation.manage',
        ]);
        $menu->addChild('Notifications', [
            'route' => 'entity.notification.collection',
        ]);
        $menu->addChild('Service tree', [
            'route' => 'entity.service_category.collection',
        ]);

        return $menu;
    }

    public function buildOptions(array $options) : array
    {
        if (!empty($options['route'])) {
            if (substr($options['route'], 0, 7) == 'entity.') {
                list($_, $entity_type, $action) = explode('.', $options['route'], 4);
                $allowed = $this->auth->isGranted('ACCESS_ENTITY_TYPE', $entity_type);
                $options['extras']['access']['entity_type'] = $allowed;
            }
        }
        return $options;
    }

    public function buildItem(ItemInterface $item, array $options) : void
    {
    }
}

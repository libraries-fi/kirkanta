<?php

namespace App\Module\Finna;

use App\EntityTypeManager;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class MenuBuilder
{
    private $factory;
    private $types;

    public function __construct(FactoryInterface $factory, EntityTypeManager $types)
    {
        $this->factory = $factory;
        $this->types = $types;
    }

    public function organisationTabs(RequestStack $request_stack) : ItemInterface
    {
        $entity = $request_stack->getCurrentRequest()->attributes->get('finna_organisation');
        $menu = $this->factory->createItem('root');

        if (!is_object($entity)) {
            $entity = $this->types->getRepository('finna_organisation')->findOneById($entity);
        }

        $menu->addChild('Basic details', [
            'route' => 'entity.finna_organisation.edit',
            'routeParameters' => [
                'finna_organisation' => $entity->getId()
            ]
        ]);

        $menu->addChild('Links', [
            'route' => 'entity.finna_organisation.link_collection',
            'routeParameters' => [
                'finna_organisation' => $entity->getId(),
            ]
        ]);

        return $menu;
    }
}

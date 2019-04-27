<?php

namespace App\Menu;

use App\Controller\OrganisationController;
use App\Menu\Breadcrumb\BreadcrumbProviderInterface;
use App\EntityTypeManager;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class BreadcrumbBuilder
{
    private $factory;
    private $types;
    private $matcher;
    private $providers;

    public function __construct(FactoryInterface $factory, EntityTypeManager $types, UrlMatcherInterface $matcher)
    {
        $this->factory = $factory;
        $this->types = $types;
        $this->matcher = $matcher;
        $this->providers = new \SplPriorityQueue();
    }

    public function addProvider(BreadcrumbProviderInterface $provider, int $priority = 0) : void
    {
        $this->providers->insert($provider, $priority);
    }

    public function build(RequestStack $request_stack) : ItemInterface
    {
        $menu = $this->factory->createItem('root');
        $menu->addChild('Kirkanta', [
            'route' => 'front',
            'extras' => [
                'translation_domain' => false
            ]
        ]);

        $current_path = $request_stack->getCurrentRequest()->getPathInfo();

        while (($i = strpos($current_path, '/', ($i ?? 0) + 1)) !== false) {
            try {
                $path = substr($current_path, 0, $i);
                $match = $this->matcher->match($path);

                if ($item = $this->getMenuItem($match)) {
                    $menu->addChild($item['label'], $item['options']);
                }
            } catch (ResourceNotFoundException $e) {
                // Thrown when there's no route for given path. Pass.
            }
        }

        return $menu;
    }

    private function getMenuItem(array $route) : ?array
    {
        foreach ($this->providers as $provider) {
            if ($provider->supports($route['_route'], $route)) {
                return $provider->getMenuItem($route['_route'], $route);
            }
        }
        return null;
    }
}

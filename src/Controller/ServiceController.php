<?php

namespace App\Controller;

use App\Entity\Service;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ServiceController extends Controller
{
    /**
     * @Route("/service/{service}/usage", name="entity.service.usage", requirements={"service": "\d+"}, defaults={"entity_type": "service"})
     * @Template("entity/Service/usage.html.twig")
     */
    public function usage(Service $service)
    {
        $templates = [];

        $instances = array_filter($service->getInstances()->toArray(), function($s) use(&$templates) {
            if (!$s->getLibrary()) {
                $templates[] = $s;
                return false;
            } else {
                return true;
            }
        });

        usort($instances, function($a, $b) {
            if ($d = strcasecmp($a->getLibrary()->getName(), $b->getLibrary()->getName())) {
                return $d;
            }
            return $a->getId() - $b->getId();
        });

        $tree = [];

        foreach ($instances as $instance) {
            $key = $instance->getLibrary()->getId();

            if (!isset($tree[$key])) {
                $tree[$key] = [
                    'library' => $instance->getLibrary(),
                    'instances' => [$instance]
                ];
            } else {
                $tree[$key]['instances'][] = $instance;
            }
        }

        return [
            'service' => $service,
            'templates' => $templates,
            'instances' => $instances,
            'tree' => $tree,
        ];
    }
}

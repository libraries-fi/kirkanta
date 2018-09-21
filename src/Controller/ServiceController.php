<?php

namespace App\Controller;

use App\EntityTypeManager;
use App\Entity\Service;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ServiceController extends Controller
{
    private $types;

    public function __construct(EntityTypeManager $types)
    {
        $this->types = $types;
    }

    /**
     * @Route("/service/{service}/usage", name="entity.service.usage", requirements={"service": "\d+"}, defaults={"entity_type": "service"})
     * @Template("entity/Service/usage.html.twig")
     */
    public function usage(Service $service)
    {
        $templates = [];
        $library_ids = [];

        $this->preloadAssociations($service->getInstances());

        $instances = array_filter($service->getInstances()->toArray(), function($s) use(&$templates, &$library_ids) {
            if (!$s->getLibrary()) {
                $templates[] = $s;
                return false;
            } else {
                $library_ids[] = $s->getLibrary()->getId();
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

    private function preloadAssociations(iterable $instances) : void
    {
        $lids = [];
        $cids = [];

        foreach ($instances as $instance) {
            if ($l = $instance->getLibrary()) {
                $lids[] = $l->getId();
            }
        }

        $libraries = $this->types->getRepository('library')
            ->createQueryBuilder('e')
            ->select('e', 'd')
            ->join('e.translations', 'd', 'WITH', 'd.langcode = :langcode')
            ->where('e.id IN (:ids)')
            ->setParameter('langcode', 'fi')
            ->setParameter('ids', $lids)
            ->getQuery()
            ->getResult();

        foreach ($libraries as $l) {
            if ($c = $l->getCity()) {
                $cids[] = $c->getId();
            }
        }

        $cities = $this->types->getRepository('city')
            ->createQueryBuilder('e')
            ->select('e', 'd')
            ->join('e.translations', 'd', 'WITH', 'd.langcode = :langcode')
            ->where('e.id IN (:ids)')
            ->setParameter('langcode', 'fi')
            ->setParameter('ids', $cids)
            ->getQuery()
            ->getResult();
    }
}

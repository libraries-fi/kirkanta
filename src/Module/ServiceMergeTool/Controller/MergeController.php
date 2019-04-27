<?php

namespace App\Module\ServiceMergeTool\Controller;

use App\EntityTypeManager;
use App\Module\ServiceMergeTool\Form\ServiceMergeForm;
use App\Util\FormData;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class MergeController extends Controller
{
    private $types;

    public function __construct(EntityTypeManager $types)
    {
        $this->types = $types;
    }

    /**
     * @Route("/admin/service-tool", name="service_tool.choose")
     * @Template("service_tool/step-1.html.twig")
     */
    public function listServices()
    {
        $services = $this->types->getRepository('service')
            ->createQueryBuilder('e')
            ->select('e', 'd')
            ->join('e.translations', 'd', 'WITH', 'd.langcode = :langcode')
            ->orderBy('d.name')
            ->setParameter('langcode', 'fi')
            ->getQuery()
            ->getResult()
            ;

        $counts = $this->types->getRepository('service')
            ->createQueryBuilder('e')
            ->select('e.id', 'COUNT(t.id) total')
            ->join('e.instances', 't')
            ->groupBy('e.id')
            ->getQuery()
            ->getScalarResult(\Doctrine\ORM\Query::HYDRATE_SINGLE_SCALAR);

        $counts = array_column($counts, 'total', 'id');

        return [
            'services' => $services,
            'instanceCounts' => $counts,
        ];
    }

    /**
     * @Route("/admin/service-tool/merge", name="service_tool.verify")
     * @Template("service_tool/step-2.html.twig")
     */
    public function mergeItems(Request $request)
    {
        $services = $this->types->getRepository('service')
            ->findBy(['id' => $request->query->get('s')]);

        usort($services, function ($a, $b) {
            return count($b->getTranslations()) - count($a->getTranslations());
        });

        $form = $this->createForm(ServiceMergeForm::class, new FormData([
            // 'keep' => 55872,
            'services' => $services,
        ]));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->types->getEntityManager();

            $keep = $form->get('keep')->getData();
            $services = $form->get('services')->getData();

            foreach ($services as $service) {
                if ($service != $keep) {
                    /*
                     * Delete translations first to avoid UNIQUE violations should the slug
                     * of $keep be changed.
                     */
                    foreach ($service->getTranslations() as $d) {
                        $em->remove($d);
                        $em->flush($d);
                    }
                }
            }

            foreach ($form->get('services')->getData() as $service) {
                if ($service != $keep) {
                    foreach ($service->getInstances() as $instance) {
                        $instance->setTemplate($keep);
                    }
                    $em->remove($service);
                }
            }

            $em->flush();
            $this->addFlash('success', 'Services were combined successfully.');

            return $this->redirectToRoute('service_tool.choose');
        }

        return [
            'services' => $services,
            'form' => $form->createView(),
        ];
    }
}

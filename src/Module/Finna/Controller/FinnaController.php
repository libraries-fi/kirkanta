<?php

namespace App\Module\Finna\Controller;

use App\EntityTypeManager;
use App\Entity\Consortium;
use App\Module\Finna\Entity\FinnaAdditions;
use App\Module\Finna\Entity\FinnaOrganisationWebsiteLink;
use App\Module\Finna\WebsiteLinkCategories;
use App\Util\SystemLanguages;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class FinnaController extends Controller
{
    const FINNA_ENTITY_TYPE = 'finna_organisation';

    private $types;

    public function __construct(EntityTypeManager $types)
    {
        $this->types = $types;
    }

    /**
     * @Route("/consortium/{consortium}/add-finna", name="entity.consortium.add_finna", defaults={"entity_type": "consortium"})
     * @ParamConverter("consortium", class="App:Consortium")
     * @Template("entity/FinnaAdditions/edit.html.twig")
     */
    public function addFinnaAdditionsAction(Request $request, Consortium $consortium)
    {
        $finna_organisation = new FinnaAdditions();
        $finna_organisation->setConsortium($consortium);

        $form = $this->types->getForm(self::FINNA_ENTITY_TYPE, 'edit', $finna_organisation, [
            'current_langcode' => $consortium->getDefaultLangcode()
        ]);

        $form->remove('exclusive');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $finna_organisation->setExclusive(false);

            /**
             * FIXME: Translations are initialized automatically but default_langcode is not...
             */
            $finna_organisation->setDefaultLangcode($consortium->getDefaultLangcode());

            $em = $this->types->getEntityManager();
            $em->persist($finna_organisation);
            $em->flush();

            $this->addFlash('form.success', 'Record was created');

            return $this->redirectToRoute('entity.finna_organisation.edit', [
                self::FINNA_ENTITY_TYPE => $finna_organisation->getId(),
            ]);
        }

        return [
            'form' => $form->createView(),
            'type_label' => $this->types->getTypeLabel(self::FINNA_ENTITY_TYPE),
            'entity_type' => 'consortium',
            'consortium' => $consortium
        ];
    }

    /**
     * Handles form submission when creating FinnaAdditions AND Consortium entities at the same time.
     * This is because the Consortium has to be persisted first in order to have access to its ID for
     * the FinnaAdditions entity to use.
     */
    public function createFinnaOrganisation(Request $request)
    {
        $finna_data = $this->types->create(self::FINNA_ENTITY_TYPE);
        $form = $this->types->getForm(self::FINNA_ENTITY_TYPE, 'edit', $finna_data, [
            'current_langcode' => SystemLanguages::TEMPORARY_LANGCODE
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $consortium = $finna_data->getConsortium();
            $consortium->setFinnaData(null);

            $em = $this->types->getEntityManager();
            $em->persist($consortium);
            $em->flush($consortium);

            // Treat all consortiums created through finna_organisation proxy as exclusives.
            $finna_data->setExclusive(true);

            $finna_data->setConsortium($consortium);

            $em->persist($finna_data);
            $em->flush();

            $this->addFlash('form.success', 'Record was created.');

            return $this->redirectToRoute('entity.finna_organisation.edit', [
                self::FINNA_ENTITY_TYPE => $finna_data->getId(),
            ]);
        } else {
            $this->addFlash('form.danger', 'Validation failed');
            return $this->redirectToRoute('entity.finna_organisation.add');
        }
    }

    /**
     * Handles form submission when creating FinnaAdditions AND Consortium entities at the same time.
     * This is because the Consortium has to be persisted first in order to have access to its ID for
     * the FinnaAdditions entity to use.
     *
     * @Route("/finna_organisation/{finna_organisation}/delete")
     * @Method("POST")
     */
    public function deleteFinnaOrganisation(Request $request, FinnaAdditions $finna_organisation)
    {
        try {
            $em = $this->getDoctrine()->getManager();

            if ($finna_organisation->isExclusive()) {
                $em->remove($finna_organisation->getConsortium());
            }

            $em->flush();
            $this->addFlash('success', 'Record was deleted.');

            return $this->redirectToRoute('entity.finna_organisation.collection');
        } catch (ForeignKeyConstraintViolationException $exception) {
            $this->addFlash('form.danger', 'Cannot delete record as it has dependencies.');

            return $this->redirectToRoute("entity.finna_organisation.edit", [
                'finna_organisation' => $finna_organisation->getId(),
            ]);
        }
    }

    /**
     * @Route("/finna_organisation/{finna_organisation}/links", name="entity.finna_organisation.link_collection")
     * @Template("entity/FinnaAdditions/links.collection.html.twig")
     */
    public function linkCollection(FinnaAdditions $finna_organisation)
    {
        $type_id = 'finna_organisation_web_link';
        $list_builder = $this->types->getListBuilder($type_id);

        $list_builder->getQueryBuilder()
            ->andWhere('e.finna_organisation = :finna_organisation')
            ->setParameter('finna_organisation', $finna_organisation);

        $result = $list_builder->load();
        $table = $list_builder->build($result);

        $groups = new WebsiteLinkCategories();
        $table
            ->addColumn('category', 'Category')
            ->useAsTemplate('category')
            ->transform('category', function ($o) use ($groups) {
                if ($label = $groups->search($o->getCategory())) {
                    return "{% trans %}{$label}{% endtrans %}";
                }
            });

        $table
            ->addColumn('weight', '')
            ->draghandle('weight');

        $table
            ->useAsTemplate('name')
            ->transform('name', function ($entity) {
                return '
                <a href="{{ path("entity.finna_organisation.edit_link", {
                    finna_organisation: row.finnaOrganisation.id,
                    link: row.id
                }) }}">{{ row.name }}</a>
            ';
            });

        $actions = [
            'add' => [
                'icon' => 'fas fa-plus-circle',
                'title' => 'Create new',
                'route' => "entity.finna_organisation.add_link",
                'params' => [
                    'finna_organisation' => $finna_organisation->getId(),
                ],
            ]
        ];

        return [
            'entity_type' => $type_id,
            'type_label' => $this->types->getTypeLabel($type_id, true),
            'table' => $table,
            'actions' => $actions,
        ];
    }

    /**
     * @Route("/finna_organisation/{finna_organisation}/links/add", name="entity.finna_organisation.add_link")
     * @Template("entity/FinnaAdditions/links.edit.html.twig")
     */
    public function addWebLink(Request $request, FinnaAdditions $finna_organisation)
    {
        $type_id = 'finna_organisation_web_link';
        $form = $this->types->getForm($type_id, 'edit');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entity = $this->types->getRepository($type_id)->create($form->getData()->getValues());
            $entity->setFinnaOrganisation($finna_organisation);

            $this->types->getEntityManager()->persist($entity);
            $this->types->getEntityManager()->flush();
            $this->addFlash('success', 'Resource created successfully.');

            return $this->redirectToRoute('entity.finna_organisation.edit_link', [
                'finna_organisation' => $finna_organisation->getId(),
                'link' => $entity->getId()
            ]);
        }

        return [
            'form' => $form->createView(),
            'entity_type' => $type_id,
            'type_label' => $this->types->getTypeLabel($type_id),
        ];
    }

    /**
     * @Route("/finna_organisation/{finna_organisation}/links/{link}/edit", name="entity.finna_organisation.edit_link")
     * @Template("entity/FinnaAdditions/links.edit.html.twig")
     */
    public function editLink(Request $request, FinnaAdditions $finna_organisation, FinnaOrganisationWebsiteLink $link)
    {
        $type_id = 'finna_organisation_web_link';

        $form = $this->types->getForm($type_id, 'edit', $link);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->types->getEntityManager()->flush();
            $this->addFlash('success', 'Changed were saved.');

            return $this->redirectToRoute('entity.finna_organisation.link_collection', [
                'finna_organisation' => $finna_organisation->getId(),
            ]);
        }

        return [
            'form' => $form->createView(),
            'type_label' => $this->types->getTypeLabel($type_id),
            'entity_type' => $type_id,
            $type_id => $link,
        ];
    }

    /**
     * @Route("/finna_organisation/{finna_organisation}/links/tablesort", name="entity.finna_organisation.table_sort")
     */
    public function tableSort(Request $request, FinnaAdditions $finna_organisation)
    {
        $ids = $request->request->get('rows') ?: [];
        $ids = array_map('intval', $ids);
        $entities = $this->types->getRepository('finna_organisation_web_link')->findById($ids);

        if ($entities) {
            usort($entities, function ($a, $b) {
                return $a->getWeight() - $b->getWeight();
            });

            $base = reset($entities)->getWeight();

            foreach ($entities as $entity) {
                $new_weight = $base + array_search($entity->getId(), $ids);
                $entity->setWeight($new_weight);
            }

            $this->types->getEntityManager()->flush();
        }

        return new JsonResponse($request->request->get('rows'));
    }
}

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
        $entity = $this->types->create($type_id);
        $form = $this->types->getForm($type_id, 'edit', $entity, [
            'current_langcode' => $finna_organisation->getDefaultLangcode()
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
            'entity' => $entity,
            'entity_type' => $type_id
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
     * @Route("/finna_organisation/{finna_organisation}/custom-data", name="entity.finna_organisation.custom_data.collection")
     * @Template("entity/FinnaAdditions/links.collection.html.twig")
     */
    public function listCustomData(FinnaAdditions $finna_organisation, \Knp\Component\Pager\PaginatorInterface $pager)
    {
        $entries = $finna_organisation->getCustomData();
        $langcode = $finna_organisation->getDefaultLangcode();

        $table = (new \App\Component\Element\Table())
            ->setColumns(['name', 'value'])
            ->useAsTemplate('name')
            ->transform('name', function ($entry) use ($finna_organisation, $langcode) {
                static $i = 0;
                $i++;

                $values = array_filter([$entry->title->{$langcode} ?? null, $entry->id]);
                $values = array_values($values);
                $label = count($values) == 2 ? "{$values[0]} ({$values[1]})" : reset($values);

                $label = htmlspecialchars($label) ?: 'NULL';

                $tokens = [
                    '{$label}' => $label,
                    '{$library_id}' => $finna_organisation->getId(),
                    '{$i}' => $i,
                ];

                return str_replace(array_keys($tokens), array_values($tokens), '<a href="{{ path("entity.finna_organisation.custom_data.edit", {finna_organisation: {$library_id}, custom_data: {$i}})}}">{$label}</a>');
            })
            ->transform('value', function ($entry) use ($langcode) {
                return $entry->value->{$langcode} ?? 'NULL';
            })
            ;

        $result = $pager->paginate($entries);
        $table->setData($result);

        return [
            'type_label' => 'Custom data',
            'entity_type' => 'custom_data',
            'table' => $table,
            'actions' => [
                'add' => [
                    'icon' => 'fas fa-plus-circle',
                    'title' => 'Create new',
                    'route' => "entity.finna_organisation.custom_data.add",
                    'params' => ['finna_organisation' => $finna_organisation->getId()]
                ]
            ]
        ];
    }

    /**
     * @Route("/finna_organisation/{finna_organisation}/custom-data/add", name="entity.finna_organisation.custom_data.add")
     * @Template("entity/FinnaAdditions/custom-data.edit.html.twig")
     */
    public function addCustomData(Request $request, FinnaAdditions $finna_organisation)
    {
        $langcodes = $finna_organisation->getTranslations()->getKeys();
        $form = $this->createForm(\App\Form\CustomDataForm::class, (object)[
            'id' => null,
            'title' => (object)[],
            'value' => (object)[],
        ], [
            'available_languages' => $langcodes,
            'current_langcode' => $finna_organisation->getDefaultLangcode()
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $toObject = function ($entry) {
                $entry = json_decode(json_encode($entry), JSON_OBJECT_AS_ARRAY);
                return (object)$entry;
            };

            $entries = $finna_organisation->getCustomData();
            $entries = array_map($toObject, $entries);
            $entries[] = $form->getData();
            $finna_organisation->setCustomData($entries);

            $this->types->getEntityManager()->flush();
            $this->addFlash('success', 'Resource created successfully.');

            return $this->redirectToRoute('entity.finna_organisation.custom_data.collection', [
                'finna_organisation' => $finna_organisation->getId()
            ]);
        }

        return [
            'type_label' => 'Custom data',
            'form' => $form->createView(),
            'entity' => $finna_organisation,
        ];
    }

    /**
     * @Route("/finna_organisation/{finna_organisation}/custom-data/{custom_data}", name="entity.finna_organisation.custom_data.edit")
     * @Template("entity/FinnaAdditions/custom-data.edit.html.twig")
     */
    public function editCustomData(Request $request, FinnaAdditions $finna_organisation, int $custom_data)
    {
        // $id is just a 1-indexed key.
        $entry = $finna_organisation->getCustomData()[$custom_data - 1];

        $langcodes = $finna_organisation->getTranslations()->getKeys();

        $form = $this->createForm(\App\Form\CustomDataForm::class, $entry, [
            'current_langcode' => SystemLanguages::DEFAULT_LANGCODE,
            'available_languages' => $langcodes
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * Doctrine does not detect a change to a single stdClass instance so we have to
             * replace the whole data source.
             */
            $toObject = function ($entry) {
                $entry = json_decode(json_encode($entry), JSON_OBJECT_AS_ARRAY);
                return (object)$entry;
            };

            $entries = $finna_organisation->getCustomData();
            $entries = array_map($toObject, $entries);

            $finna_organisation->setCustomData($entries);
            $this->types->getEntityManager()->flush();
            $this->addFlash('success', 'Changes were saved.');

            return $this->redirectToRoute("entity.finna_organisation.custom_data.collection", [
                'finna_organisation' => $finna_organisation->getId()
            ]);
        }

        return [
            'type_label' => 'Custom data',
            'entity' => $finna_organisation,
            'custom_data' => $entry,
            'custom_data_pos' => $custom_data,
            'form' => $form->createView(),
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

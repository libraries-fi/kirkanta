<?php

namespace App\Controller;

use App\Entity\Library;
use App\Entity\Feature\Weight;
use App\EntityTypeManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;

class OrganisationController extends Controller
{
    use Feature\ProvideEntityTypeManager;

    public static $resources = [
        'departments' => 'department',
        'email_addresses' => 'email_address',
        'links' => 'web_link',
        'periods' => 'period',
        'persons' => 'person',
        'phone_numbers' => 'phone',
        'pictures' => 'organisation_photo',
        'services' => 'service_instance'
    ];

    private $types;

    public function __construct(EntityTypeManager $types)
    {
        $this->types = $types;
    }

    /**
     * @ParamConverter("library", converter="entity_from_type_and_id")
     */
    public function resourceCollection(Request $request, $library, string $entity_type, string $resource)
    {
        $entity_class = $this->types->getEntityClass($entity_type);
        $resource_class = $this->types->getEntityClass($this->resolveResourceTypeId($entity_type, $resource));
        $type_id = $this->resolveResourceTypeId($entity_type, $resource);
        $list_builder = $this->types->getListBuilder($type_id);

        if ($this->types->hasForm($type_id, 'search')) {
            $search_form = $this->types->getForm($type_id, 'search', null, ['parent' => $library]);
            $search_form->remove('group');
            $search_form->handleRequest($request);

            if ($search_form->isSubmitted() && $search_form->isValid()) {
                $list_builder->setSearch($search_form->getData()->getValues());
            }
        } else {
            $search_form = null;
        }

        /*
         * NOTE: Use alias 'ox' to avoid collision with aliases internally used by
         * the list builder
         */
        $builder = $list_builder
            ->getQueryBuilder()
            ->innerJoin($entity_class, 'ox', 'WITH', "
                ox.id = :parent AND
                e MEMBER OF ox.{$resource}
            ")
            ;

        $builder->setParameter('parent', $library);

        if (in_array($type_id, ['service_instance'])) {
            // By default the list builder wants to fetch only shared templates,
            // here we want just the opposite.
            $builder->setParameter('shared', false);
        }

        $result = $list_builder->paginate($builder);
        $table = $list_builder->build($result)
            ->removeColumn('group')
            ->removeColumn('parent')
            ->removeColumn('library')
            ->removeColumn('owner');

        if (is_a($resource_class, Weight::class, true)) {
            $table->addColumn('weight', '')->dragHandle('weight');
            $builder->orderBy('weight');
        }

        $actions = [];

        switch ($resource) {
            case 'departments':
            case 'pictures':
                $table->useAsTemplate('name');
                $table->transform('name', function($entity) use ($entity_type, $resource) {
                    return str_replace(['%entity_type%', '%resource%'], [$entity_type, $resource], '
                        <a href="{{ path("entity.%entity_type%.%resource%.edit", {
                            %entity_type%: row.parent.id,
                            resource: app.request.get("resource"),
                            resource_id: row.id
                        }) }}">{{ row.name }}</a>
                    ');
                });
                break;

            case 'email_addresses':
            case 'links':
            case 'phone_numbers':
            case 'periods':
                $table->useAsTemplate('name');
                $table->transform('name', function($entity) use($entity_type) {
                    return '
                        {% set entity_type = app.request.get("entity_type") %}
                        {% set resource = app.request.get("resource") %}
                        {% set route_name = "entity.%s.%s.edit"|format(entity_type, resource) %}

                        <a href="{{ path(route_name, {
                            (entity_type): row.parent.id,
                            resource: resource,
                            resource_id: row.id
                        }) }}">{{ row.name }}</a>

                        {% if row.department %}
                            <small class="text-secondary d-block">{{ row.department.name }}</small>
                        {% endif %}
                    ';
                });
                break;

            case 'persons':
                $table->useAsTemplate('name');
                $table->transform('name', function($entity) {
                    return '
                        {% set entity_type = app.request.get("entity_type") %}
                        {% set resource = app.request.get("resource") %}
                        {% set route_name = "entity.%s.%s.edit"|format(entity_type, resource) %}

                        <a href="{{ path(route_name, {
                            (entity_type): row.library.id,
                            resource: resource,
                            resource_id: row.id
                        }) }}">{{ row.listName }}</a>
                    ';
                });
                break;

            case 'services':
                $table->useAsTemplate('standard_name');
                $table->transform('standard_name', function($entity) {
                    return '
                        {% set entity_type = app.request.get("entity_type") %}
                        {% set resource = app.request.get("resource") %}
                        {% set route_name = "entity.%s.%s.edit"|format(entity_type, resource) %}

                        <a href="{{ path(route_name, {
                            (entity_type): row.parent.id,
                            resource: resource,
                            resource_id: row.id
                        }) }}">{{ row.standardName }}</a>
                    ';
                });

                $actions['import'] = [
                    'title' => 'From template',
                    'route' => "entity.{$entity_type}.resource_from_template",
                    'params' => [$entity_type => $library->getId(), 'resource' => $resource],
                    'icon' => 'fas fa-copy',
                ];
                break;

            default:
                throw new \Exception("Unhandled case '{$resource}'");
        }

        $actions['add'] = [
            'title' => 'Create new',
            'route' => "entity.{$entity_type}.{$resource}.add",
            'params' => [$entity_type => $library->getId()],
            'icon' => 'fas fa-plus-circle',
        ];

        return [
            'table' => $table,
            'search_form' => $search_form ? $search_form->createView() : null,
            'actions' => $actions
        ];
    }

    /**
     * @ParamConverter("library", converter="entity_from_type_and_id")
     */
    public function addResource(Request $request, $library, string $entity_type, string $resource)
    {
        $type_id = $this->resolveResourceTypeId($entity_type, $resource);
        $form = $this->types->getForm($type_id, 'edit', null, [
            'context_entity' => $library
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entity = $this->types->getRepository($type_id)->create($form->getData()->getValues());

            if (method_exists($entity, 'setParent')) {
                $entity->setParent($library);
            }

            $this->types->getEntityManager()->persist($entity);
            $this->types->getEntityManager()->flush();

            $this->addFlash('success', 'Resource created successfully.');

            return $this->redirectToRoute("entity.{$entity_type}.{$resource}.edit", [
                $entity_type => $library->getId(),
                'resource_id' => $entity->getId(),
            ]);
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @ParamConverter("library", converter="entity_from_type_and_id")
     */
    public function editResource(Request $request, $library, string $entity_type, string $resource, int $resource_id)
    {
        var_dump(get_class($request->getSession()->getFlashBag()));
        $type_id = $this->resolveResourceTypeId($entity_type, $resource);
        $entity = $this->types->getRepository($type_id)->findOneBy(['id' => $resource_id]);

        $form = $this->types->getForm($type_id, 'edit', $entity, [
            'context_entity' => $library
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
          $this->types->getEntityManager()->flush();
          $this->addFlash('success', 'Changes were saved.');

          return $this->redirectToRoute("entity.{$entity_type}.{$resource}", [
            $entity_type => $library->getId(),
          ]);
        }

        return [
            'form' => $form->createView(),
            'type_label' => $this->types->getTypeLabel($type_id),
            'entity_type' => $type_id,
            $type_id => $entity,
        ];
    }

    /**
     * @Route("/library/{library}/{resource}/{resource_id}/delete", name="entity.library.delete_resource", requirements={"library": "\d+", "resource": "[a-z]\w+", "resource_id": "\d+"}, defaults={"type": "organisation"})
     */
    public function deleteResource(Request $request, int $id, string $resource, int $resource_id)
    {
        exit('delete resource');
    }

    /**
     * @Route("/library/{library}/{resource}/import", name="entity.library.resource_from_template", defaults={"entity_type": "library"}, requirements={"library": "\d+", "resource": "[a-z]\w+"})
     * @ParamConverter("library", converter="entity_from_type_and_id")
     * @Template("entity/Library/resource.import.html.twig")
     */
    public function createResourceFromTemplate(Request $request, Library $library, string $entity_type, string $resource)
    {
        $type_id = $this->resolveResourceTypeId($entity_type, $resource);
        $entity_class = $this->types->getEntityClass($type_id);

        $form = $this->types->getForm($type_id, 'import', null, [
            'user_groups' => $library->getOwner()->getTree(),
            'entity_type' => $this->types->getEntityClass($type_id),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $templates = $form->get('templates')->getData();
            $em = $this->types->getEntityManager();

            foreach ($templates as $template) {
                $instance = new $entity_class;
                $instance->setOwner($library->getOwner());
                $instance->setLibrary($library);

                switch ($type_id) {
                    case 'service_instance':
                        $instance->setTemplate($template->getTemplate());
                        $instance->setForLoan($template->isForLoan());
                        $instance->setPhoneNumber($template->getPhoneNumber());
                        $instance->setEmail($template->getEmail());
                        $instance->setPicture($template->getPicture());

                        foreach ($template->getTranslations() as $langcode => $data) {
                            $translation = new $data($langcode);
                            $translation->setName($data->getName());
                            $translation->setDescription($data->getDescription());
                            $translation->setEntity($instance);
                            $instance->getTranslations()->set($langcode, $translation);

                            $em->persist($translation);
                        }

                        $library->getServices()->add($instance);
                        break;

                    default:
                        throw new \Exception("Unhandled type '{$type_id}'");
                }
                $em->persist($instance);
            }

            $em->flush();
            $this->addFlash('success', 'Resources imported successfully.');

            return $this->redirectToRoute("entity.library.{$resource}", [
              'id' => $id,
            ]);
        }

        return [
            'entity_type' => 'service_instance',
            'type_label' => $this->types->getTypeLabel($type_id),
            'form' => $form->createView(),
        ];
    }

    /**
     * @Method("POST")
     */
    public function tableSort(Request $request, string $resource, EntityTypeManager $manager)
    {
        $ids = $request->request->get('rows') ?: [];
        $ids = array_map('intval', $ids);
        $entities = $manager->getRepository(self::$resources[$resource])->findById($ids);

        if ($entities) {
            usort($entities, function($a, $b) {
                return $a->getWeight() - $b->getWeight();
            });

            $base = reset($entities)->getWeight();

            foreach ($entities as $entity) {
                $new_weight = $base + array_search($entity->getId(), $ids);
                $entity->setWeight($new_weight);
            }

            $manager->getEntityManager()->flush();
        }

        return new JsonResponse($request->request->get('rows'));
    }

    /**
     * @Route("/library/{library}/custom_data", name="entity.library.custom_data")
     * @ParamConverter("entity", converter="entity_from_type_and_id")
     */
    public function listCustomData(Request $request, Library $library, \Knp\Component\Pager\PaginatorInterface $pager)
    {
        // var_dump($router->getRouteCollection()->get('entity.library.edit')->compile()->getVariables());
        $entries = $library->getCustomData();

        $table = (new \App\Component\Element\Table)
            ->setColumns(['name', 'value'])
            ->useAsTemplate('name')
            ->transform('name', function($entry) use($library) {
                $values = array_filter([$entry->getName(), $entry->getId()]);
                $values = array_values($values);
                $label = count($values) == 2 ? "{$values[0]} ({$values[1]})" : reset($values);
                $label = htmlspecialchars($label) ?: 'NULL';

                return str_replace(['{$label}', '{$library_id}'], [$label, $library->getId()], '<a href="{{ path("entity.custom_data.edit", {library: {$library_id}, custom_data: row.pos})}}">{$label}</a>');
            })
            ;

        $result = $pager->paginate($entries);
        $table->setData($result);

        return [
            'type_label' => 'Custom data',
            'entity_type' => 'custom_data',
            'table' => $table,
            'actions' => []
        ];
    }

    /**
     * @Route("/library/{library}/custom_data/{custom_data}", name="entity.custom_data.edit")
     * @ParamConverter("entity", converter="entity_from_type_and_id")
     */
    public function editCustomData(Request $request, Library $library, int $custom_data)
    {
        // $id is just a 1-indexed key.
        $entry = $library->getCustomData()[$custom_data - 1];

        $form = $this->createFormBuilder($entry)
            // ->add('name', null)
            ->add('id')
            ->add('value')
            ->add('actions', \App\Form\Type\ActionsType::class, [
                'mapped' => false,
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getEntityTypeManager()->getEntityManager()->flush();
        }

        return [
            'type_label' => 'Custom data',
            'entity_type' => 'custom_data',
            'custom_data' => $entry,
            'form' => $form->createView(),
            'actions' => []
        ];

        var_dump($entry);
        exit;
    }

    /**
     * @Route("/library/{library}/custom_data/{custom_data}", name="entity.custom_data.delete")
     * @ParamConverter("entity", converter="entity_from_type_and_id")
     */
    public function deleteCustomData()
    {

    }

    /**
     * @Template("entity/Library/contact-info.index.html.twig")
     */
    public function contactsTab(Library $library)
    {

    }

    protected function resolveResourceTypeId(string $parent_type, string $resource_name) : string
    {
        $entity_class = $this->types->getEntityClass($parent_type);
        $metadata = $this->getEntityManager()->getClassMetadata($entity_class);
        $resource_class = $metadata->associationMappings[$resource_name]['targetEntity'];
        $type_id = $this->types->getTypeId($resource_class);
        return $type_id;
    }
}

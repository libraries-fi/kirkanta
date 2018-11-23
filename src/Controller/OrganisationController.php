<?php

namespace App\Controller;

use App\Entity\Library;
use App\Entity\LibraryInterface;
use App\Entity\Feature\Weight;
use App\EntityTypeManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use Knp\Component\Pager\PaginatorInterface;

class OrganisationController extends Controller
{
    use Feature\ProvideEntityTypeManager;

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
            case 'contact_groups':
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

        switch ($resource) {
            case 'periods':
            case 'services':
              $actions['import'] = [
                  'title' => 'From template',
                  'route' => "entity.{$entity_type}.{$resource}.from_template",
                  'params' => [$entity_type => $library->getId(), 'resource' => $resource],
                  'icon' => 'fas fa-copy',
              ];
        }

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

            if (method_exists($entity, 'setLibrary')) {
                $entity->setLibrary($library);
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

            'parent_entity_type' => $entity_type,
            'parent_entity' => $library,
            'resource_type' => $resource,
        ];
    }

    /**
     * @ParamConverter("library", converter="entity_from_type_and_id")
     */
    public function deleteResource(Request $request, $library, string $entity_type, string $resource, int $resource_id)
    {
        $type_id = $this->resolveResourceTypeId($entity_type, $resource);

        $response = $this->forward(EntityController::class . '::delete', [
            'entity_type' => $type_id,
            $type_id => $resource_id,
        ]);

        return $response;
    }

    /**
     * @ParamConverter("library", converter="entity_from_type_and_id")
     * @Template("entity/Library/resource.import.html.twig")
     */
    public function resourceFromTemplate(Request $request, LibraryInterface $library, string $entity_type, string $resource)
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
                    case 'period':
                        $instance->setIsLegacyFormat($template->isLegacyFormat());
                        $instance->setDays($template->getDays());
                        $instance->setValidFrom(clone $template->getValidFrom());

                        if ($ends = $template->getValidUntil()) {
                            $instance->setValidUntil(clone $ends);
                        }

                        foreach ($template->getTranslations() as $langcode => $data) {
                            $translation = new $data($langcode);
                            $translation->setName($data->getName());
                            $translation->setDescription($data->getDescription());
                            $translation->setEntity($instance);
                            $instance->getTranslations()->set($langcode, $translation);

                            $em->persist($translation);
                        }

                        $library->getPeriods()->add($instance);
                        break;

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

            return $this->redirectToRoute("entity.{$entity_type}.{$resource}", [
              $entity_type => $library->getId(),
            ]);
        }

        return [
            'entity_type' => $type_id,
            'type_label' => $this->types->getTypeLabel($type_id),
            'form' => $form->createView(),
        ];
    }

    /**
     * @Method("POST")
     * @ParamConverter("library", converter="entity_from_type_and_id")
     */
    public function tableSort(Request $request, LibraryInterface $library, string $resource)
    {
        $accessor = \Symfony\Component\PropertyAccess\PropertyAccess::createPropertyAccessor();
        $resources = $accessor->getValue($library, $resource);
        $reorder = $request->request->get('rows') ?: [];

        $matched = $resources->filter(function($r) use($reorder) {
            return in_array($r->getId(), $reorder);
        });

        if (count($matched) > 0) {
            $start_index = $matched->first()->getWeight();

            foreach ($matched as $entity) {
                $weights[$entity->getId()] = $start_index + array_search($entity->getId(), $reorder);
            }
        } else {
            $weights = [];
        }

        $this->types->getRepository((new \App\Util\LibraryResources)->offsetGet($resource))
            ->updateWeights($resources, $weights);

        /*
         * Collection will be re-initialized the next time it's accessed.
         * Because indexing is run in post-op hook, the collection will be initialized with
         * updated weights.
         */
        $resources->setInitialized(false);

        $this->types->getEntityManager()->flush();

        return new JsonResponse($request->request->get('rows'));
    }

    /**
     * @ParamConverter("entity", converter="entity_from_type_and_id")
     */
    public function listCustomData(Request $request, string $entity_type, $entity, \Knp\Component\Pager\PaginatorInterface $pager)
    {
        // var_dump($router->getRouteCollection()->get('entity.library.edit')->compile()->getVariables());
        $entries = $entity->getCustomData();

        $table = (new \App\Component\Element\Table)
            ->setColumns(['name', 'value'])
            ->useAsTemplate('name')
            ->transform('name', function($entry) use($entity, $entity_type) {
                static $i = 0;
                $i++;

                $values = array_filter([$entry->title, $entry->id]);
                $values = array_values($values);
                $label = count($values) == 2 ? "{$values[0]} ({$values[1]})" : reset($values);
                $label = htmlspecialchars($label) ?: 'NULL';

                $tokens = [
                    '{$entity_type}' => $entity_type,
                    '{$label}' => $label,
                    '{$library_id}' => $entity->getId(),
                    '{$i}' => $i,
                ];

                return str_replace(array_keys($tokens), array_values($tokens), '<a href="{{ path("entity.{$entity_type}.custom_data.edit", {{$entity_type}: {$library_id}, custom_data: {$i}})}}">{$label}</a>');
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
                    'route' => "entity.{$entity_type}.custom_data.add",
                    'params' => [$entity_type => $entity->getId()]
                ]
            ]
        ];
    }

    /**
     * @ParamConverter("entity", converter="entity_from_type_and_id")
     * @Template("entity/Library/custom-data.edit.html.twig")
     */
    public function addCustomData(Request $request, string $entity_type, LibraryInterface $entity)
    {
        $form = $this->createForm(\App\Form\CustomDataForm::class, (object)[
            'title' => null,
            'id' => null,
            'value' => null,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entries = $entity->getCustomData();
            $entries[] = $form->getData();
            $entity->setCustomData($entries);

            $this->getEntityTypeManager()->getEntityManager()->flush();
            $this->addFlash('success', 'Resource created successfully.');

            return $this->redirectToRoute("entity.{$entity_type}.custom_data", [
                $entity_type => $entity->getId()
            ]);
        }

        return [
            'type_label' => 'Custom data',
            'form' => $form->createView(),
            'entity' => $entity,
        ];
    }

    /**
     * @ParamConverter("entity", converter="entity_from_type_and_id")
     * @Template("entity/Library/custom-data.edit.html.twig")
     */
    public function editCustomData(Request $request, string $entity_type, LibraryInterface $entity, int $custom_data)
    {
        // $id is just a 1-indexed key.
        $entry = $entity->getCustomData()[$custom_data - 1];

        $form = $this->createForm(\App\Form\CustomDataForm::class, $entry);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * Doctrine does not detect a change to a single stdClass instance so we have to
             * replace the whole data source.
             */
            $data = unserialize(serialize($entity->getCustomData()));

            $entity->setCustomData($data);
            $this->getEntityTypeManager()->getEntityManager()->flush();
            $this->addFlash('success', 'Changes were saved.');

            return $this->redirectToRoute("entity.{$entity_type}.custom_data", [
                $entity_type => $entity->getId()
            ]);
        }

        return [
            'type_label' => 'Custom data',
            'entity' => $entity,
            // 'entity_type' => 'custom_data',
            'custom_data' => $entry,
            'custom_data_pos' => $custom_data,
            'form' => $form->createView(),
        ];
    }

    /**
     * @ParamConverter("entity", converter="entity_from_type_and_id")
     * @Template("entity/Library/custom-data.delete.html.twig")
     */
    public function deleteCustomData(Request $request, string $entity_type, LibraryInterface $entity, int $custom_data)
    {
        $form = $this->createFormBuilder()
            ->add('submit', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, [
                'label' => 'Delete',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $entity->getCustomData();
            unset($data[$custom_data - 1]);
            $entity->setCustomData($data);

            $this->getEntityTypeManager()->getEntityManager()->flush();
            $this->addFlash('success', 'Record was deleted.');

            return $this->redirectToRoute("entity.{$entity_type}.custom_data", [
                $entity_type => $entity->getId()
            ]);
        }

        return [
            'type_label' => 'Custom data',
            'form' => $form->createView(),
            'entity' => $entity,
            'custom_data_pos' => $custom_data,
        ];
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

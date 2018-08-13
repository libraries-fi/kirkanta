<?php

namespace App\Controller;

use App\Entity\Library;
use App\EntityTypeManager;
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

    private $resources = [
        'departments' => 'department',
        'periods' => 'period',
        'persons' => 'person',
        'phone_numbers' => 'phone',
        'pictures' => 'organisation_photo',
        'services' => 'service_instance'
    ];

    /**
     * @Route("/library/{library}/{resource}", name="entity.library.list_resources", requirements={"library": "\d+", "resource": "departments|periods|persons|phone_numbers|pictures|services"})
     * @ParamConverter("library", class="App:Library")
     * @Template("entity/Library/resources-list.html.twig")
     */
    public function listResourcesAction(Request $request, Library $library, string $resource)
    {
        // var_dump($request->attributes);
        $types = $this->getEntityTypeManager();
        $metadata = $this->getEntityManager()->getClassMetadata(Library::class);
        $resource_class = $metadata->associationMappings[$resource]['targetEntity'];
        $type_id = $types->getTypeId($resource_class);
        $list_builder = $types->getListBuilder($type_id);


        /*
         * NOTE: Use alias 'ox' to avoid collision with aliases internally used by
         * the list builder
         */
        $builder = $list_builder
            ->getQueryBuilder()
            ->innerJoin(Library::class, 'ox', 'WITH', "
                ox.id = :library AND
                e MEMBER OF ox.{$resource}
            ")
            ;

        $builder->setParameter('library', $library);

        if (in_array($type_id, ['service_instance'])) {
            // By default the list builder wants to fetch only shared templates,
            // here we want just the opposite.
            $builder->setParameter('shared', false);
        }

        $result = $list_builder->paginate($builder);
        $table = $list_builder->build($result);

        $actions = [];

        switch ($resource) {
            case 'departments':
            case 'periods':
            case 'pictures':
            case 'phone_numbers':
                $table->useAsTemplate('name');
                $table->transform('name', function($entity) {
                    return '<a href="{{ path("entity.library.edit_resource", {
                        "library": row.library.id,
                        "resource": app.request.get("resource"),
                        "resource_id": row.id
                    }) }}">{{ row.name }}</a>';
                });
                break;

            case 'persons':
                $table->useAsTemplate('name');
                $table->transform('name', function($entity) {
                    return '<a href="{{ path("entity.library.edit_resource", {
                        "library": row.library.id,
                        "resource": app.request.get("resource"),
                        "resource_id": row.id
                    }) }}">{{ row.listName }}</a>';

                });
                break;

            case 'services':
                $table->useAsTemplate('standard_name');
                $table->transform('standard_name', function($entity) {
                    return '<a href="{{ path("entity.library.edit_resource", {
                        "library": row.library.id,
                        "resource": app.request.get("resource"),
                        "resource_id": row.id
                    }) }}">{{ row.standardName }}</a>';

                });

                $actions['import'] = [
                    'title' => 'From template',
                    'route' => 'entity.library.resource_from_template',
                    'params' => ['library' => $library->getId(), 'resource' => $resource],
                    'icon' => 'fas fa-copy',
                ];
                break;

            default:
                throw new \Exception("Unhandled case '{$resource}'");
        }

        $actions['add'] = [
            'title' => 'Create new',
            'route' => 'entity.library.add_resource',
            'params' => ['library' => $library->getId(), 'resource' => $resource],
            'icon' => 'fas fa-plus-circle',
        ];

        return [
            'type_label' => $types->getTypeLabel($type_id, true),
            'entity_type' => $type_id,
            'table' => $table,
            'actions' => $actions
        ];
    }

    /**
     * @Route("/library/{library}/{resource}/add", name="entity.library.add_resource", requirements={"library": "\d+", "resource": "departments|periods|persons|phone_numbers|pictures|services"})
     * @ParamConverter("library", class="App:Library")
     * @Template("entity/Library/resources-edit.html.twig")
     */
    public function addResourceAction(Request $request, Library $library, string $resource)
    {
        $type_id = $this->resources[$resource];
        $types = $this->get('entity_type_manager');
        $form = $types->getForm($type_id, 'edit', ['library' => $library]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entity = $types->getRepository($type_id)->create($form->getData());

            // Is this needed or is it enough to pass 'library' to the form object?
            if (method_exists($entity, 'setLibrary')) {
                $entity->setLibrary($library);
            }

            $types->getEntityManager()->persist($entity);
            $types->getEntityManager()->flush();

            $this->addFlash('success', 'Resource created successfully.');

            return $this->redirectToRoute('entity.library.edit_resource', [
                'id' => $library->getId(),
                'resource' => $resource,
                'resource_id' => $entity->getId(),
            ]);
        }

        $template = $this->resolveTemplate('edit', $resource);

        return $this->render($template, [
            'form' => $form->createView(),
            'type_label' => $types->getTypeLabel($type_id),
            'entity_type' => $type_id,
        ]);
    }

    /**
     * @Route("/library/{library}/{resource}/{resource_id}", name="entity.library.edit_resource", requirements={"library": "\d+", "resource_id": "\d+", "resource": "departments|periods|persons|phone_numbers|pictures|services"})
     * @ParamConverter("library", class="App:Library")
     */
    public function editResourceAction(Request $request, string $resource, int $resource_id)
    {
        $type_id = $this->resources[$resource];
        $types = $this->get('entity_type_manager');
        $entity = $types->getRepository($type_id)->findOneBy(['id' => $resource_id]);
        $form = $types->getForm($type_id, 'edit', $entity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
          $this->get('doctrine.orm.entity_manager')->flush();
          $this->addFlash('success', 'Changes were saved.');

          return $this->redirectToRoute('entity.library.list_resources', [
            'id' => $id,
            'resource' => $resource,
          ]);
        }

        $template = $this->resolveTemplate('edit', $resource);

        return $this->render($template, [
            'form' => $form->createView(),
            'type_label' => $types->getTypeLabel($type_id),
            'entity_type' => $type_id,
            $type_id => $entity,
        ]);
    }

    /**
     * @Route("/library/{library}/{resource}/{resource_id}/delete", name="entity.library.delete_resource", requirements={"library": "\d+", "resource": "[a-z]\w+", "resource_id": "\d+"}, defaults={"type": "organisation"})
     */
    public function deleteResourceAction(Request $request, int $id, string $resource, int $resource_id)
    {
        exit('delete resource');
    }

    /**
     * @Route("/library/{library}/{resource}/import", name="entity.library.resource_from_template", requirements={"library": "\d+", "resource": "[a-z]\w+"})
     * @ParamConverter("library", class="App:Library")
     * @Template("entity/Library/resources-import.html.twig")
     */
    public function createResourceFromTemplate(Request $request, Library $library, string $resource)
    {
        $type_id = $this->resources[$resource];
        $types = $this->get('entity_type_manager');
        $entity_class = $types->getEntityClass($type_id);
        $template = $this->resolveTemplate('import', $resource);

        $form = $types->getForm($type_id, 'import', null, [
            'user_groups' => $library->getOwner()->getTree(),
            'entity_type' => $types->getEntityClass($type_id),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $templates = $form->get('templates')->getData();
            $em = $types->getEntityManager();

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

            return $this->redirectToRoute('entity.library.list_resources', [
              'id' => $id,
              'resource' => $resource,
            ]);
        }

        return $this->render($template, [
            'type_label' => $types->getTypeLabel($type_id),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/library/{library}/{resource}/tablesort", name="entity.library.resource_table_sort", requirements={"library": "\d+", "resource": "[a-z]\w+"}, defaults={"type": "organisation"})
     */
    public function tableSort(Request $request, int $id, string $resource, EntityTypeManager $manager)
    {
        $ids = $request->request->get('rows') ?: [];
        $ids = array_map('intval', $ids);
        $entities = $manager->getRepository($this->resources[$resource])->findById($ids);

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
     * @ParamConverter("library", class="App:Library")
     * @Template("entity/Library/resources-list.html.twig")
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
     * @ParamConverter("library", class="App:Library")
     * @Template("entity/Library/resources-edit.html.twig")
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
     * @ParamConverter("library", class="App:Library")
     * @Template("entity/Library/resources-edit.html.twig")
     */
    public function deleteCustomData()
    {

    }

    protected function resolveTemplate(string $action, string $resource_type) : string
    {
        $loader = $this->get('twig')->getLoader();
        $resource_type = str_replace('_', '-', $resource_type);

        $names = [
            sprintf('entity/Library/%s-%s.html.twig', $resource_type, $action),
            sprintf('entity/Library/resources-%s.html.twig', $action),
        ];

        foreach ($names as $name) {
            if ($loader->exists($name)) {
                return $name;
            }
        }
    }
}

<?php

namespace App\Controller;

use App\EntityTypeManager;
use App\Entity\Feature\CreatedAwareness;
use App\Entity\Feature\ModifiedAwareness;
use App\Entity\Feature\GroupOwnership;
use App\Entity\Feature\Sluggable;
use App\Entity\Feature\StateAwareness;
use App\Form\I18n\EntityTranslationForm;
use App\Util\FormData;
use App\Util\Slugger;
use App\Util\SystemLanguages;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class EntityController extends Controller
{
    private $entityTypeManager;

    public function __construct(EntityTypeManager $types)
    {
        $this->entityTypeManager = $types;
    }

    public function collection(Request $request, string $entity_type, bool $recycled = false)
    {
        $types = $this->entityTypeManager;
        $list_builder = $this->entityTypeManager->getListBuilder($entity_type);

        if ($this->entityTypeManager->hasForm($entity_type, 'search')) {
            $search_form = $this->entityTypeManager->getForm($entity_type, 'search', null, ['admin' => true]);
            $search_form->handleRequest($request);

            if ($search_form->isSubmitted() && $search_form->isValid()) {
              $list_builder->setSearch($search_form->getData()->getValues());
            }
        } else {
            $search_form = null;
        }

        $list_builder->setShowRecycledOnly($recycled);

        $result = $list_builder->load();
        $table = $list_builder->build($result);
        $template = $this->resolveTemplate('list', $entity_type);

        $actions = [
            'add' => [
                'title' => 'Create new',
                'route' => "entity.{$entity_type}.add",
                'icon' => 'fas fa-plus-circle'
            ]
        ];

        // if (is_a($this->entityTypeManager->getEntityClass($entity_type), StateAwareness::class, true)) {
        //     $actions['recycled'] = [
        //         'title' => 'Recycled',
        //         'route' => "entity.{$entity_type}.recycled",
        //         'icon' => 'fa fa-trash'
        //     ];
        // }

        return $this->render($template, [
            'type_label' => $this->entityTypeManager->getTypeLabel($entity_type, true),
            'search_form' => $search_form ? $search_form->createView() : null,
            'table' => $table,
            'actions' => $actions,
            'entity_type' => $entity_type,
        ]);
    }

    public function add(Request $request, string $entity_type)
    {
        $form = $this->entityTypeManager->getForm($entity_type, 'edit', new FormData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entity = $this->entityTypeManager
                ->getRepository($entity_type)
                ->create($form->getData()->getValues());

            $this->entityTypeManager->getEntityManager()->persist($entity);
            $this->entityTypeManager->getEntityManager()->flush();
            $this->addFlash('form.success', 'Record was created.');

            return $this->redirectToRoute('entity.edit', [
                'entity_type' => $entity_type,
                'id' => $entity->getId(),
            ]);
        }

        $template = $this->resolveTemplate('edit', $entity_type);

        return $this->render($template, [
            'type_label' => $this->entityTypeManager->getTypeLabel($entity_type),
            'form' => $form->createView(),
            'entity_type' => $entity_type,
        ]);
    }

    /**
     * @ParamConverter("entity", converter="entity_from_type_and_id")
     */
    public function edit(Request $request, string $entity_type, $entity)
    {
        $form = $this->entityTypeManager->getForm($entity_type, 'edit', $entity);
        $form->handleRequest($request);

        if (!empty($_POST)) {
            // var_dump($form->get('ptv')->getData());
            // exit;
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityTypeManager->getEntityManager()->flush();
            $this->addFlash('form.success', 'Changes were saved.');

            return $this->redirectToRoute("entity.{$entity_type}.edit", [
                'entity_type' => $entity_type,
                $entity_type => $entity->getId()
            ] + $request->query->all());
        }

        $langcode = $request->query->get('langcode');

        if ($langcode && !$entity->hasTranslation($langcode)) {
            $this->addFlash('form.warning', 'Creating a new translation');
        }

        $template = $this->resolveTemplate('edit', $entity_type);

        return $this->render($template, [
            'type_label' => $this->entityTypeManager->getTypeLabel($entity_type),
            'form' => $form->createView(),
            'entity_type' => $entity_type,
            $entity_type => $entity,
        ])->setStatusCode($form->isSubmitted() ? 422 : 200);
    }

    /**
     * @ParamConverter("entity", converter="entity_from_type_and_id")
     */
    public function translate(Request $request, string $entity_type, $entity)
    {
        $form = $this->createForm(EntityTranslationForm::class, null, [
            'existing_translations' => $entity->getTranslations()->getKeys()
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute("entity.{$entity_type}.edit", [
                $entity_type => $entity->getId(),
                'langcode' => $form->get('langcode')->getData(),
            ]);
        }

        $template = $this->resolveTemplate('translate', $entity_type);

        return $this->render($template, [
            'form' => $form->createView(),
            'type_label' => $this->entityTypeManager->getTypeLabel($entity_type),
            'entity_type' => $entity_type,
            $entity_type => $entity,

            // Here this should be OK as there should be no variants of this route.
            'entity' => $entity,
        ]);
    }

    /**
     * @ParamConverter("entity", converter="entity_from_type_and_id")
     */
    public function delete(Request $request, string $entity_type, $entity)
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
            if ($entity instanceof StateAwareness) {
                $entity->setState(StateAwareness::DELETED);
            } else {
                $this->entityTypeManager->getEntityManager()->remove($entity);
            }

            try {
                $this->entityTypeManager->getEntityManager()->flush();
                $this->addFlash('success', 'Record was deleted.');

                return $this->redirectToRoute("entity.{$entity_type}.collection");
            } catch (ForeignKeyConstraintViolationException $exception) {
                $this->addFlash('form.danger', 'Cannot delete record as it has dependencies.');

                return $this->redirectToRoute("entity.{$entity_type}.edit", [
                    $entity_type => $entity->getId(),
                ]);
            }
        }

        $template = $this->resolveTemplate('delete', $entity_type);

        return $this->render($template, [
            'type_label' => $this->entityTypeManager->getTypeLabel($entity_type),
            'form' => $form->createView(),
            'entity_type' => $entity_type,
            $entity_type => $entity,
        ]);
    }

    /**
     * @Method("POST")
     * @ParamConverter("entity", converter="entity_from_type_and_id")
     */
    public function restore(string $entity_type, StateAwareness $entity)
    {
        $entity->setState(StateAwareness::DRAFT);
        $this->getDoctrine()->getManager()->flush();
        $this->addFlash('form.success', 'Record was restored. It is now marked as a draft.');

        return $this->redirectToRoute("entity.{$entity_type}.edit", [
            $entity_type => $entity->getId()
        ]);
    }

    /**
     * @Route("/{entity_type}/slugger", name="entity.slugger", requirements={"entity_type": "\w+"})
     * @Method("POST")
     */
    public function generateSlug(Request $request, string $entity_type)
    {
        $name = $request->request->get('name');
        $langcode = $request->request->get('langcode');

        $data_class = $this->entityTypeManager->getEntityClass($entity_type) . 'Data';
        $storage = $this->entityTypeManager->getEntityManager()->getRepository($data_class);
        $slugger = new Slugger($storage);

        $slug = $slugger->makeSlug($name, $langcode);
        return new JsonResponse($slug);
    }

    protected function resolveTemplate(string $action, string $entity_type) : string
    {
        $loader = $this->get('twig')->getLoader();
        $entity_class = $this->entityTypeManager->getEntityClass($entity_type);
        $class_name = substr(strrchr($entity_class, '\\'), 1);

        $names = [
            sprintf('@ServiceTree/entity/%s/%s.html.twig', $class_name, $action),
            sprintf('entity/%s/%s.html.twig', $class_name, $action),
            sprintf('entity/%s.html.twig', $action),
        ];

        foreach ($names as $name) {
            if ($loader->exists($name)) {
                return $name;
            }
        }

        throw new RuntimeException("Could not resolve template for {$entity_type}:{$action}");
    }
}

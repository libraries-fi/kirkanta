<?php

namespace KirjastotFi\KirkantaApiBundle\Controller;

use App\Entity\Feature\StateAwareness;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use KirjastotFi\KirkantaApiBundle\QueryCompiler;
use KirjastotFi\KirkantaApiBundle\Validator\Constraints\LanguageAllowed;
use OutOfBoundsException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Doctrine\Common\Collections\ArrayCollection;

use App\EntityTypeManager;
use App\Util\SystemLanguages;

class RestController extends FOSRestController
{
    /**
     * @Get("/{entity_type}.{_format}", defaults={"_format": NULL}, requirements={"entity_type": "organisation|service|consortium|library"})
     * @QueryParam(name="page_number", key="page", requirements="\d+", default="1")
     * @QueryParam(name="page_size", key="limit", requirements="\d+", default="10")
     * @QueryParam(name="langcode", key="lang", requirements=@LanguageAllowed, nullable=true, strict=true)
     */
    public function search(Request $request, EntityTypeManager $manager, QueryCompiler $compiler, string $entity_type, int $page_number, int $page_size, string $langcode = null)
    {
        // var_dump($page_size, $page_number, $langcode);
        $entity_class = $manager->getEntityClass($entity_type);
        $form = $this->getForm($entity_type);
        $form->submit($request->query->all(), true);

        if (!$form->isValid()) {
            $errors = [];
            var_dump($form->getErrors(true));

            foreach ($form->getErrors(true) as $key => $iterator) {
                if ($iterator instanceof \iterable) {
                    foreach ($iterator as $ckey => $cerror) {
                        $errors[$cerror->getOrigin()->getName()][$ckey] = $cerror->getMessage();
                    }
                } else {
                    if (is_integer($key)) {
                        if ($parent = $iterator->getOrigin()->getParent()) {
                            $key = $iterator->getOrigin()->getParent()->getName();
                        } else {
                            $key = 'form';
                        }
                    }
                    $errors[$key] = $iterator->getMessage();
                }
            }
            return $this->view($errors, 400);
        }

        $values = $form->getData();
        $view_mode = $values['view'];
        $with = $values['with'] ?? [];
        $refs = $values['refs'] ?? [];
        unset($values['view']);

        // Need to fallback on langcode to ensure that we receive unique IDs.
        // Can't use 'DISTINCT' when sorting by translated values.
        $query = $compiler->compile($entity_type, $values, $langcode ?? SystemLanguages::DEFAULT_LANGCODE);










        $query->setFirstResult($page_size * ($page_number - 1));
        $query->setMaxResults($page_size);
        // $dql = $query->getQuery()->getSql();
        $result = $query->getQuery()->getResult();
        $documents = array_column($result, 'cached_document');

        $total = $query->select('COUNT(DISTINCT e.id)')
            ->resetDqlPart('orderBy')
            ->getQuery()->getSingleScalarResult();

        if ($langcode) {
            $documents = array_map(function(array $document) use($langcode) { return Tool::filter_translations($document, $langcode); }, $documents);

            // $documents = iterator_to_array(new FilterTranslationsIterator($documents, $langcode));


            // $documents = array_column($documents, 'address');

            // header('Content-Type: text/plain; charset=UTF-8');
            // print_r($documents);
            // exit;

        }

        $view = [
            'type' => $entity_type,
            'total' => $total,
            'pager' => [
                'per_page' => $page_size,
                'current' => $page_number,
                'last' => ceil($total / $page_size),
            ],
            'result' => $documents,
        ];

        // header('Content-Type: application/json');
        // print json_encode($view, JSON_PRETTY_PRINT);
        // exit;


        // $context = (new Context)->setGroups(array_merge([$view_mode], $with))
        //     ->setAttribute('refs', $refs)
        //     ->setAttribute('langcode', $langcode);

        return $this->view($view);





        // var_dump(($result));
        // header('Content-Type: text/plain; charset=UTF-8');

        // printf("%d\n\n", count($result));
        // print_r(array_keys($result));
        // print($dql);

        // exit("\n\nOK");




















        exit('PAUSE');

        $init = clone $query;
        // $init->setFirstResult($page_size * ($page_number - 1));
        // $init->setMaxResults($page_size);
        // $init->select('e');
        // $init->orderBy('e.id');
        // $init->distinct('e.id');

        $pager = $this->get('knp_paginator')->paginate($init, $page_number, $page_size, [
            // 'wrap-queries' => true,
            // 'distinct' => false,
        ]);

        print count($pager);

        exit("\n\nPAGED");

        $result_ids = [];

        foreach ($pager as $row) {
            // $result_ids[] = $row->getId();
            $result_ids[] = $row['entity']->getId();
        }

        $query->andWhere('e.id IN (:entity_ids)');
        $query->setParameter('entity_ids', $result_ids);
        $query->setFirstResult(NULL);
        $query->setMaxResults(NULL);

        $result = $query->getQuery()->getResult();
        $items = [];

        foreach ($result as $row) {
            $items[] = $row['entity'];
        }

        $view = [
            'type' => $entity_type,
            'total' => $pager->getTotalItemCount(),
            'pager' => [
                'current' => $pager->getCurrentPageNumber(),
                'last' => ceil($pager->getTotalItemCount() / $pager->getItemNumberPerPage()),
                'per_page' => $pager->getItemNumberPerPage(),
            ],
            // Using a collection here to fix XML serialization.
            'result' => new ArrayCollection($items),
        ];

        $context = (new Context)->setGroups(array_merge([$view_mode], $with))
            ->setAttribute('refs', $refs)
            ->setAttribute('langcode', $langcode);

        return $this->view($view)->setContext($context);
    }

    /**
     * @GET("/{entity_type}/{id}.{_format}", defaults={"_format": NULL}, requirements={"id": "\d+", "entity_type": "organisation|service|consortium|library"})
     * @QueryParam(name="langcode", key="lang", requirements=@LanguageAllowed, nullable=true, strict=true)
     */
    public function fetchOne(Request $request, EntityTypeManager $manager, string $entity_type, int $id, string $langcode = null)
    {
        $form = $this->getForm($entity_type);
        $form->submit($request->query->all(), true);
        $values = $form->getData();

        $view_mode = $values['view'];
        $with = $values['with'];
        $refs = $values['refs'];

        $entity = $manager->getRepository($entity_type)->findOneById($id);

        if (!$entity || ($entity instanceof StateAwareness && !$entity->isPublished())) {
            throw new NotFoundHttpException;
        }

        $context = (new Context)->setGroups(array_merge([$view_mode], $with))
            ->setAttribute('refs', $refs)
            ->setAttribute('langcode', $langcode);

        $view = [
            'type' => $entity_type,
            'result' => $entity,
        ];

        return $this->view($view)->setContext($context);
    }

    protected function getForm(string $entity_type, string $method = 'get')
    {
        $config = $this->getParameter('kirkanta_api.entity_forms');

        if (!isset($config[$entity_type])) {
            throw new OutOfBoundsException(sprintf('Invalid type \'%s\'', $entity_type));
        }
        $form_class = $config[$entity_type][$method];
        $form = $this->createForm($form_class, null, [
            'method' => 'GET',
            'allow_extra_fields' => true,
        ]);

        return $form;
    }
}

class Tool {
    public static function filter_translations(array &$document, $accepted_langcode) {
        $languages = array_map(function() { return false; }, array_flip((new SystemLanguages)->getData()));
        $languages[$accepted_langcode] = true;

        foreach ($document as $field => &$data) {
            if (is_array($data)) {
                foreach ($data as $langcode => $values) {
                    if (isset($languages[$langcode])) {
                        $document[$field] = $data[$accepted_langcode] ?? null;
                        break;
                    } elseif (is_array($values)) {
                        Tool::filter_translations($data, $accepted_langcode);
                    }
                }
            }
        }

        return $document;
    }
}

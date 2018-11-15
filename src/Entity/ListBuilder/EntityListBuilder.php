<?php

namespace App\Entity\ListBuilder;

use App\Component\Element\Table;
use App\Entity\Feature\GroupOwnership;
use App\Entity\Feature\StateAwareness;
use App\Entity\Feature\Translatable;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

abstract class EntityListBuilder
{
    private $storage;
    private $paginator;
    private $request_stack;
    private $auth;

    private $query;

    private $page_size;
    private $search;
    private $sorting;

    protected $recycledOnly = false;

    protected $langcode = 'fi';

    public function __construct(EntityRepository $storage, PaginatorInterface $paginator, RequestStack $request_stack, AuthorizationCheckerInterface $auth, TokenStorageInterface $tokens, int $page_size = 50)
    {
        $this->storage = $storage;
        $this->paginator = $paginator;
        $this->request_stack = $request_stack;
        $this->auth = $auth;
        $this->tokens = $tokens;

        $this->page_size = $page_size;
        $this->search = [];
        $this->sorting = [];
    }

    public function setShowRecycledOnly(bool $state) : void
    {
        $this->recycledOnly = $state;
    }

    public function build(iterable $data) : iterable
    {
        return (new Table($data))->setInitialSorting($this->sorting);
    }

    public function load() : iterable
    {
        return $this->paginate($this->getQueryBuilder());
    }

    public function sort(QueryBuilder $builder) : QueryBuilder
    {
        $request = $this->request_stack->getCurrentRequest();
        $this->sorting = $this->getSorting($request);

        if ($this->sorting) {
            $builder->resetDQLPart('orderBy');
        }

        foreach ($this->sorting as $key => $direction) {
            if (!strpos($key, '.')) {
                $key = "e.{$key}";
            }
            $builder->addOrderBy($key, $direction);
        }
        $builder->addOrderBy('e.id', $direction ?? 'asc');
        return $builder;
    }

    public function paginate(QueryBuilder $builder) : iterable
    {
        $request = $this->request_stack->getCurrentRequest();
        $page = $request->query->getInt('p', 1);

        /*
         * NOTE: Enabling 'wrap-queries' will cause the entity manager to perform
         * an additional query per each row, that is bad.
         * Alternative is to disable 'distinct', which will be OK as long as we
         * join to data table with langcode.
         */
        $pager = $this->paginator->paginate($builder, $page, $this->page_size, [
            // 'wrap-queries' => true,
            'distinct' => false,
        ]);
        return $pager;
    }

    public function getQueryBuilder() : QueryBuilder
    {
        if (!$this->query) {
            $this->query = $this->sort($this->createQueryBuilder());
        }
        return $this->query;
    }

    protected function createQueryBuilder() : QueryBuilder
    {
        $builder = $this->storage->createQueryBuilder('e');
        $entity_class = $this->storage->getClassName();

        if (is_a($entity_class, GroupOwnership::class, true)) {
            if (!$this->auth->isGranted('MANAGE_ALL_ENTITIES', $entity_class)) {
                $user_groups = $this->tokens->getToken()->getUser()->getGroup()->getTree();
                $builder->andWhere('e.group IN (:owner)');
                $builder->setParameter('owner', $user_groups);
            }
        }

        if (is_a($entity_class, StateAwareness::class, true)) {
            if ($this->recycledOnly) {
                $builder->andWhere('e.state = -1');
            } else {
                $builder->andWhere('e.state >= 0');
            }
        }

        if (is_a($entity_class, Translatable::class, true)) {
            $builder->addSelect('d')->join('e.translations', 'd', 'WITH', 'd.langcode = e.default_langcode');
            // $builder->setParameter('langcode', $this->langcode);
        }

        return $builder;
    }

    protected function pageSize() : int
    {
        return $this->page_size;
    }

    public function setSearch(array $values) : void
    {
        $this->search = $values;
    }

    public function getSearch() : array
    {
        return $this->search;
    }

    public function getSorting(Request $request) : array
    {
        $columns = $this->build([])->getColumns();

        if (($sort_key = $request->query->get('s')) && isset($columns[$sort_key])) {
            $columns = [$sort_key => $columns[$sort_key]];
        } else {
            $filtered = array_filter($columns, function($column) {
                return $column->getSorting();
            });
            if (!$filtered) {
                $filtered = array_filter($columns, function($column) {
                    return $column->isSortable();
                });
            }
            $columns = $filtered;
        }

        if ($columns) {
            $column = reset($columns);
            $sort_dir = $request->query->get('d', $column->getSorting()) ?? 'asc';
            $keys = $column->getMapping();
            $sorting = array_combine($keys, array_pad([], count($keys), $sort_dir));
            $column->setSorting($sort_dir);
            return $sorting;
        } else {
            return [];
        }
    }
}

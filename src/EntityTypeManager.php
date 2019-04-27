<?php

namespace App;

use App\Entity\ListBuilder\EntityListBuilder;
use App\Util\FormData;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Proxy\Proxy;
use Knp\Component\Pager\PaginatorInterface;
use OutOfBoundsException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class EntityTypeManager
{
    private $entity_manager;
    private $form_factory;
    private $paginator;
    private $request_stack;
    private $auth;
    private $tokens;
    private $types;

    public function __construct(EntityManagerInterface $entity_manager, FormFactoryInterface $form_factory, PaginatorInterface $paginator, RequestStack $request_stack, AuthorizationCheckerInterface $auth, TokenStorageInterface $tokens, array $entity_types = [])
    {
        $this->entity_manager = $entity_manager;
        $this->form_factory = $form_factory;
        $this->paginator = $paginator;
        $this->request_stack = $request_stack;
        $this->auth = $auth;
        $this->tokens = $tokens;
        $this->types = [];

        foreach ($entity_types as $type) {
            $this->addType($type['id'], $type);
        }
    }

    public function getTypes() : array
    {
        return $this->types;
    }

    public function addType(string $type_id, array $definition) : void
    {
        $this->types[$type_id] = $definition;
    }

    public function getTypeLabel(string $type_id, bool $plural = false) : string
    {
        $key = $plural ? 'label_multiple' : 'label';
        return $this->getType($type_id)[$key];
    }

    public function getTypeId(string $entity_class) : string
    {
        if (is_a($entity_class, Proxy::class, true)) {
            $parents = class_parents($entity_class);
            $entity_class = reset($parents);
        }

        foreach ($this->types as $type_id => $definition) {
            if ($definition['class_name'] == $entity_class) {
                return $type_id;
            }
        }
        throw new OutOfBoundsException(sprintf("Entity class '{$entity_class}' is not managed"));
    }

    public function getEntityClass(string $type_id) : string
    {
        return $this->getType($type_id)['class_name'];
    }

    public function getEntityManager() : EntityManagerInterface
    {
        return $this->entity_manager;
    }

    public function getRepository(string $type_id) : EntityRepository
    {
        $class = $this->getEntityClass($type_id);
        return $this->entity_manager->getRepository($class);
    }

    public function getListBuilder(string $type_id) : EntityListBuilder
    {
        $class = $this->getType($type_id)['list_builder'];
        $storage = $this->getRepository($type_id);
        return new $class($storage, $this->paginator, $this->request_stack, $this->auth, $this->tokens);
    }

    public function getFormClass(string $type_id, string $form_id = 'default')
    {
        $forms = $this->getType($type_id)['forms'];
        $form_class = $forms[$form_id] ?? $forms['default'];
        return $form_class;
    }

    public function hasForm(string $type_id, string $form_id) : bool
    {
         return isset($this->getType($type_id)['forms'][$form_id]);
    }

    public function getForm(string $type_id, string $form_id = 'default', $data = null, array $options = [])
    {
        if (is_null($data) || is_array($data)) {
            // $data = new FormData($data ?? []);
        }

        $form_class = $this->getFormClass($type_id, $form_id);
        return $this->form_factory->create($form_class, $data, $options);
    }

    public function create(string $type_id, array $values = [])
    {
        return $this->getRepository($type_id)->create($values);
    }

    /**
     * @deprecated
     */
    public function createQueryBuilder(?string $type_id, ?string $alias)
    {
        if ($type_id) {
            return $this->getRepository($type_id)->createQueryBuilder($alias);
        } else {
            return $this->entity_manager->createQueryBuilder();
        }
    }

    public function selectQuery(string $type_id, string $alias = 'e') : QueryBuilder
    {
        return $this->getRepository($type_id)->createQueryBuilder($alias);
    }

    public function updateQuery(string $type_id, string $alias = 'e') : QueryBuilder
    {
        $entity_class = $this->getEntityClass($type_id);
        return $this->entity_manager->createQueryBuilder()->update($entity_class, $alias);
    }

    protected function getType(string $type_id) : array
    {
        if (!isset($this->types[$type_id])) {
            throw new OutOfBoundsException("Invalid type '{$type_id}'");
        }
        return $this->types[$type_id];
    }
}

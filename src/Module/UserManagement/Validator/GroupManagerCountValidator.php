<?php

namespace App\Module\UserManagement\Validator;

use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class GroupManagerCountValidator extends ConstraintValidator
{
    private $repository;

    public function __construct(EntityManagerInterface $entities)
    {
        $this->repository = $entities->getRepository('App:User');
    }

    public function validate($value, Constraint $constraint) : void
    {
        if ($value) {
            $object = $this->context->getObject();

            if ($object instanceof FormInterface) {
                $group = $object->getParent()->get('group')->getData();
            } elseif ($object instanceof UserInterface) {
                $group = $object->getGroup();
            } else {
                throw new RuntimeException('Invalid context object for validator');
            }

            $users = $this->repository->findBy([
                'group' => $group
            ]);

            $managers = array_filter($users, (function($u) use($object) {
                if ($u == $object) {
                    return false;
                }
                return in_array(GroupManagerCount::GROUP_MANAGER_ROLE, $u->getRoles());
            }));

            if (count($managers) >= $group->getMaxGroupAdmins()) {
                $this->context->buildViolation($constraint->message)
                ->setParameter('{{ max }}', $group->getMaxGroupAdmins())
                ->addViolation();
            }
        }
    }
}

<?php

namespace App\Security\Authorization\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

use App\EntityTypeManager;
use App\Entity\Consortium;
use App\Entity\Library;
use App\Entity\Period;
use App\Entity\Person;
use App\Entity\ServiceInstance;
use App\Module\Finna\Entity\FinnaAdditions;
use App\Security\Authorization\SystemRoles;

class EntityTypeVoter extends Voter
{
    const ACCESS_ATTRIBUTE = 'ACCESS_ENTITY_TYPE';
    const MANAGE_ATTRIBUTE = 'MANAGE_ALL_ENTITIES';

    private $classMap = [
        Library::class => [],
        Period::class => [],
        Person::class => [],
        ServiceInstance::class => [],
        FinnaAdditions::class => ['ROLE_FINNA'],
    ];

    public function __construct(EntityTypeManager $types)
    {
        $this->types = $types;
    }

    protected function supports($attribute, $subject) : bool
    {
        return in_array($attribute, [self::ACCESS_ATTRIBUTE, self::MANAGE_ATTRIBUTE]);
    }

    protected function voteOnAttribute($attribute, $entity_type, TokenInterface $token) : bool
    {
        $user_roles = $token->getUser()->getRoles(true);

        if ($attribute == self::ACCESS_ATTRIBUTE) {
            if (!class_exists($entity_type)) {
                $entity_type = $this->types->getEntityClass($entity_type);
            }

            $required_roles = $this->classMap[$entity_type] ?? [SystemRoles::ADMIN_ROLE];

            if (empty($required_roles)) {
                return $token->isAuthenticated();
            } else {
                $matches = array_intersect($user_roles, $required_roles);

                /*
                * NOTE: For now it's undecided whether it's enough to possess any of the roles or
                * should it be required to have all of them.
                */
                return count($matches) == count($required_roles);
            }
        } else {
            return in_array(SystemRoles::ADMIN_ROLE, $user_roles, true);
        }
    }
}

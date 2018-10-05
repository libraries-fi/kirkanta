<?php

namespace App\Security\Authorization\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

use App\EntityTypeManager;
use App\Entity\Feature\GroupOwnership;
use App\Security\Authorization\SystemRoles;

class EntityAccessVoter extends Voter
{
    const EDIT_ATTRIBUTE = 'edit';

    public function __construct(EntityTypeManager $types)
    {
        $this->types = $types;
    }

    protected function supports($attribute, $subject) : bool
    {
        return $attribute == self::EDIT_ATTRIBUTE && $subject instanceof GroupOwnership;
    }

    protected function voteOnAttribute($attribute, $entity, TokenInterface $token) : bool
    {
        if ($user = $token->getUser()) {
            $groups = $token->getUser()->getGroup()->getTree();
            return in_array($entity->getOwner(), $user->getGroup()->getTree());
        } else {
            return false;
        }
    }
}

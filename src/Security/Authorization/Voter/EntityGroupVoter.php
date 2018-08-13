<?php

namespace App\Security\Authorization\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

use App\Entity\Feature\GroupOwnership;

class EntityGroupVoter extends Voter
{
    protected function supports($attribute, $subject) : bool
    {
        return is_object($subject) && $subject instanceof GroupOwnership;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) : bool
    {
        foreach ($token->getUser()->getGroup()->getTree() as $group) {
            if ($subject->getGroup()->getId() == $group->getId()) {
                return true;
            }
        }

        return false;
    }
}

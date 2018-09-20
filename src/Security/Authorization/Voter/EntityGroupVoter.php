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
        if (!$subject->getGroup()) {
            // Some templates (services, periods etc.) are shared globally.
            // Access to these is retricted to admins.
            // But here we should probably be able to make a distinction between viewing and editing.
            return false;
        }

        foreach ($token->getUser()->getGroup()->getTree() as $group) {
            if ($subject->getGroup()->getId() == $group->getId()) {
                return true;
            }
        }

        return false;
    }
}

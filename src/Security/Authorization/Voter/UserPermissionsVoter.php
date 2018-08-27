<?php

namespace App\Security\Authorization\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class UserPermissionsVoter implements VoterInterface
{
    public function vote(TokenInterface $token, $subject, array $attributes) : int
    {
        foreach ($token->getRoles() as $role) {
            if ($role->getRole() == 'ROLE_ROOT') {
                return self::ACCESS_GRANTED;
            }
        }

        return self::ACCESS_ABSTAIN;
    }
}

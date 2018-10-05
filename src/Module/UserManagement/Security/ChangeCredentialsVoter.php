<?php

namespace App\Module\UserManagement\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ChangeCredentialsVoter extends Voter
{
    const CHANGE_PASSWORD = 'CHANGE_PASSWORD';
    const CHANGE_EMAIL = 'CHANGE_EMAIL';

    protected function supports($attribute, $subject) : bool
    {
        return $attribute == self::CHANGE_PASSWORD || $attribute == self::CHANGE_EMAIL;
    }

    protected function voteOnAttribute($attribute, $edited_user, TokenInterface $token) : bool
    {
        // $edited_user is null if called from e.g. @IsGranted.

        $current_user = $token->getUser();
        $edited_user = $edited_user ?? $current_user;

        if ($edited_user->isMunicipalAccount()) {
            return false;
        }

        if ($current_user == $edited_user) {
            return true;
        }

        return false;
    }
}

<?php

namespace App\Module\UserManagement;

use Symfony\Component\Security\Core\Validator\Constraints;

/**
 * Used as a data object with forms.
 */
class ChangePassword
{
    /**
     * @Constraints\UserPassword
     */
    public $old_password;
    
    public $new_password;

    public function __construct(string $old_password)
    {
        $this->old_password = $old_password;
    }

    public function getOldPassword() : string
    {
        return $this->old_password;
    }

    public function setNewPassword(string $password) : void
    {
        $this->new_password = $password;
    }

    public function getNewPassword() : ?string
    {
        return $this->new_password;
    }
}

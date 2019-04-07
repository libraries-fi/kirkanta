<?php

namespace UserAccountsBundle;

use DateTime;
use DateTimeInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

interface UserInterface extends AdvancedUserInterface
{
    const GROUP_MANAGER_ROLE = 'ROLE_GROUP_MANAGER';

    public function getUsername() : ?string;
    public function setUsername(string $username) : void;

    public function getPassword() : ?string;
    public function setPassword(string $hash) : void;

    public function getEmail() : ?string;
    public function setEmail(string $email) : void;

    public function getCreated() : DateTime;
}

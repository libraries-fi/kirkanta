<?php

namespace UserAccountsBundle;

use DateTime;
use DateTimeInterface;
use Symfony\Component\Security\Core\User\UserInterface as CoreUserInterface;

interface UserInterface extends CoreUserInterface
{
    public function getUsername() : string;
    public function setUsername(string $username) : void;

    public function getPassword() : ?string;
    public function setPassword(string $hash) : void;

    public function getEmail() : string;
    public function setEmail(string $email) : void;

    public function getCreated() : DateTime;
}

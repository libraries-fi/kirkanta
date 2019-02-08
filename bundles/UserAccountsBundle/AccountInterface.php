<?php

namespace UserAccountsBundle;

interface AccountInterface
{
    public function isActive() : bool;
    public function getUsername() : ?string;
}

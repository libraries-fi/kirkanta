<?php

namespace App\Module\UserManagement;

use App\Module\Email\EmailInterface;
use App\Module\UserManagement\Entity\OneTimeToken;
use UserAccountsBundle\UserInterface;

class AccountCreatedEmail extends \Swift_Message implements EmailInterface
{
    private $token;
    private $user;

    public function __construct(OneTimeToken $token)
    {
        parent::__construct('User account created');

        $this->token = $token;
        $this->user = $token->getUser();

        $this->setSender('hakemisto@kirjastot.fi', 'Kirkanta');
        $this->setTo($this->user->getEmail(), $this->user->getUsername());
    }

    public function getTemplate() : string
    {
        return 'email/account-created.html.twig';
    }

    public function getTemplateParameters() : array
    {
        return [
            'user' => $this->user,
            'token' => $this->token->getToken(),
        ];
    }
}

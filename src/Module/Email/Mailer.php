<?php

namespace App\Module\Email;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class Mailer
{
    private $backend;
    private $renderer;

    public function __construct(\Swift_Mailer $backend, EngineInterface $renderer)
    {
        $this->backend = $backend;
        $this->renderer = $renderer;
    }
    
    public function send(EmailInterface $email) : void
    {
        $content = $this->renderer->render($email->getTemplate(), $email->getTemplateParameters());
        $email->setBody($content, 'text/html');

        $this->backend->send($email);
    }
}

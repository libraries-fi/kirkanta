<?php

namespace App\Module\Email;

interface EmailInterface
{
    public function getTemplate() : string;
    public function getTemplateParameters() : array;
}

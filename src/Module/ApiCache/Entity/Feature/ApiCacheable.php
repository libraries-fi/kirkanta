<?php

namespace App\Module\ApiCache\Entity\Feature;

interface ApiCacheable
{
    public function getApiDocument() : ?array;
    public function setApiDocument(array $document) : void;
}

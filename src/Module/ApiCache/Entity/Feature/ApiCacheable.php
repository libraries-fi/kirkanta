<?php

namespace App\Module\ApiCache\Entity\Feature;

interface ApiCacheable
{
    public function getCachedDocument() : ?array;
    public function setCachedDocument(array $document) : void;
}

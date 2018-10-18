<?php

namespace App\Module\ApiCache\Entity\Feature;

use Doctrine\ORM\Mapping as ORM;

trait ApiCacheableTrait
{
    /**
     * @ORM\Column(type="json_array")
     */
    protected $api_document;

    public function getApiDocument() : ?array
    {
        return $this->api_document;
    }

    public function setApiDocument(array $document) : void
    {
        $this->api_document = $document;
    }
}

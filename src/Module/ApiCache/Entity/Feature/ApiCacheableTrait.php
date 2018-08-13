<?php

namespace App\Module\ApiCache\Entity\Feature;

use Doctrine\ORM\Mapping as ORM;

trait ApiCacheableTrait
{
    /**
     * @ORM\Column(type="json_array")
     */
    protected $cached_document;

    public function getCachedDocument() : ?array
    {
        return $this->cached_document;
    }

    public function setCachedDocument(array $document) : void
    {
        $this->cached_document = $document;
    }
}

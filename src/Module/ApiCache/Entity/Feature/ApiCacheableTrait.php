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

    public function getApiKeywords() : ?array
    {
        if ($this->supportsApiKeywords()) {
            return $this->api_keywords;
        } else {
            return null;
        }
    }

    public function supportsApiKeywords() : bool
    {
        return property_exists($this, 'api_keywords');
    }

    public static function extractApiKeywords(array $document, ...$paths) : array
    {
        $values = [];

        foreach ($paths as $path) {
            $data = $document;

            foreach ($path as $key) {
                $data = $data[$key] ?? null;
            }

            if ($data) {
                $values[] = mb_strtolower($data);
            }
        }

        return array_unique($values);
    }


    public static function extractApiKeywordsArray(array $documents, ...$paths) : array {
        $values = [];

        foreach ($documents as $document) {
            $values = array_merge($values, static::extractApiKeywords($document, ...$paths));
        }

        return $values;
    }
}

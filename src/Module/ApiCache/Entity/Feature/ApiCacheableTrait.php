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
                if (is_array($data)) {
                    // Handle translated fields.
                    $values = array_merge($values, array_map('mb_strtolower', array_values($data)));
                } else {
                    $values[] = mb_strtolower($data);
                }
            }
        }

        // NOTE: Don't remove duplicates as word instance count increases document importance.
        return $values;
    }


    public static function extractApiKeywordsArray(array $documents, ...$paths) : array
    {
        $values = [];

        foreach ($documents as $document) {
            $values = array_merge($values, static::extractApiKeywords($document, ...$paths));
        }

        return $values;
    }
}

<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use App\I18n\Translations;

abstract class EntityDataBase
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    protected $langcode;

    /**
     * This property should be re-mapped in subclass.
     */
    protected $entity;

    public function __construct(string $langcode = null)
    {
        $this->langcode = $langcode;
    }

    public function getId() : ?int
    {
        if ($this->entity) {
            return $this->entity->getId();
        } else {
            return $null;
        }
    }

    public function getLangcode() : string
    {
        return $this->langcode;
    }

    public function setLangcode(string $langcode) : void
    {
        $this->langcode = $langcode;
    }
}

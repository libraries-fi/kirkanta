<?php

namespace App\Entity;

use App\Entity\Feature\Translatable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

class CustomData implements Translatable
{
    use Feature\TranslatableTrait;

    private $pos;

    private $id;
    private $value;
    private $translations;

    public static function fromArray(array $values) : CustomData
    {
        $instance = new static();
        $instance->setId($values['id']);
        $instance->setValue($values['value'] ?? null);
        $instance->setTranslations($values['translations'] ?? []);
        $instance->setPos($values['pos'] ?? 0);
        return $instance;
    }

    public function __constructor()
    {
        $this->translations = new ArrayCollection();
    }

    public function getPos() : int
    {
        return $this->pos;
    }

    public function setPos(int $pos) : void
    {
        $this->pos = $pos;
    }

    public function getId() : ?string
    {
        return $this->id;
    }

    public function setId(?string $id) : void
    {
        $this->id = $id;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value) : void
    {
        $this->value = $value;
    }

    public function getName() : ?string
    {
        return $this->translations[$this->langcode]['name'] ?? null;
    }

    public function setName(string $name) : void
    {
        $this->translations[$this->langcode]['name'] = $name;
    }

    public function setTranslations(iterable $data) : void
    {
        if (!($data instanceof Collection)) {
            $data = new ArrayCollection($data);
        }

        $this->translations = $data;
    }
}

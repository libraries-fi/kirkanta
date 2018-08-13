<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

abstract class EntityBase
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    public function __construct()
    {
        if ($this instanceof Feature\CreatedAwareness) {
            $this->created = new DateTime;
        }

        if ($this instanceof Feature\Translatable) {
            $this->setTranslations(new ArrayCollection);
        }
    }

    public function getId() : ?int
    {
        return $this->id;
    }

    public function isNew() : bool
    {
        return $this->id === null;
    }
}

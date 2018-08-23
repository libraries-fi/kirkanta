<?php

namespace App\Entity;

use App\Entity\Feature\Translatable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="departments")
 */
class Department extends EntityBase implements Translatable
{
    use Feature\TranslatableTrait;

    /**
     * @ORM\ManyToOne(targetEntity="Library", inversedBy="departments")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="Period", mappedBy="department", cascade={"persist", "remove"})
     */
    private $periods;

    /**
     * @ORM\OneToMany(targetEntity="PhoneNumber", mappedBy="parent", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    // private $phone_numbers;

    /**
     * @ORM\OneToMany(targetEntity="DepartmentData", mappedBy="entity", orphanRemoval=true, cascade={"persist", "remove"}, fetch="EXTRA_LAZY", indexBy="langcode")
     */
    protected $translations;

    public function __toString()
    {
        return $this->getName();
    }

    public function getName() : string
    {
        return $this->translations[$this->langcode]->getName();
    }

    public function setName(string $name) : void
    {
        $this->translations[$this->langcode]->setName($name);
    }

    public function getDescription() : ?string
    {
        return $this->translations[$this->langcode]->getDescription();
    }

    public function setDescription(?string $description) : void
    {
        $this->translations[$this->langcode]->setDescription($description);
    }

    public function getLibrary() : Library
    {
        return $this->parent;
    }

    public function setLibrary(Library $organisation) : void
    {
        $this->parent = $organisation;
    }

    public function getParent() : Library
    {
        return $this->getLibrary();
    }
}

<?php

namespace App\Entity;

use App\Entity\Feature\Translatable;
use App\Entity\Feature\Weight;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"phone"="PhoneNumber"})
 */
abstract class ContactInfo extends EntityBase implements Translatable, Weight
{
    use Feature\TranslatableTrait;
    use Feature\WeightTrait;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    private $contact;

    /**
     * @ORM\OneToMany(targetEntity="ContactInfoData", mappedBy="entity", orphanRemoval=true, cascade={"persist", "remove"}, fetch="EXTRA_LAZY", indexBy="langcode")
     * @Assert\Valid
     */
    private $translations;

    public function getContact() : string
    {
        return $this->contact;
    }

    public function setContact(string $contact) : void
    {
        $this->contact = $contact;
    }

    public function getName() : string
    {
        return $this->translations[$this->langcode]->getName();
    }

    public function setName($name) : void
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

    public function setLibrary(Library $organisation) : void
    {
        $this->library = $organisation;
    }

    public function getLibrary() : Library
    {
        return $this->library;
    }

    public function setDepartment(Department $department) : void
    {
        $this->department = $department;
    }

    public function getDepartment() : Department
    {
        return $this->department;
    }

    public function getTranslations() : Collection
    {
        return $this->translations;
    }
}

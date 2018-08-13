<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Feature\CreatedAwareness;
use App\Entity\Feature\GroupOwnership;
use App\Entity\Feature\StateAwareness;
use App\Entity\Feature\Translatable;
use App\I18n\Translations;

/**
 * @ORM\Entity
 * @ORM\Table(name="persons")
 */
class Person extends EntityBase implements CreatedAwareness, GroupOwnership, StateAwareness, Translatable
{
    use Feature\CreatedAwarenessTrait;
    use Feature\GroupOwnershipTrait;
    use Feature\StateAwarenessTrait;
    use Feature\TranslatableTrait;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private $last_name;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private $first_name;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="boolean")
     */
    private $email_public = true;

    /**
     * @ORM\Column(type="json_array")
     */
    private $qualities;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private $url;

    /**
     * @ORM\Column(type="boolean", name="is_head")
     */
    private $head = false;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $picture;

    /**
     * @ORM\OneToMany(targetEntity="PersonData", mappedBy="entity", orphanRemoval=true, cascade={"persist", "remove"}, fetch="EXTRA_LAZY", indexBy="langcode")
     */
    private $translations;

    /**
     * @ORM\ManyToOne(targetEntity="Library", inversedBy="persons")
     */
    private $library;

    public function getName() : string
    {
        return sprintf('%s %s', $this->first_name, $this->last_name);
    }

    public function getListName() : string
    {
        return sprintf('%s, %s', $this->last_name, $this->first_name);
    }

    public function getLastName() : string
    {
        return $this->last_name;
    }

    public function setLastName(string $name) : void
    {
        $this->last_name = $name;
    }

    public function getFirstName() : string
    {
        return $this->first_name;
    }

    public function setFirstName(string $name) : void
    {
        $this->first_name = $name;
    }

    public function getJobTitle() : ?string
    {
        return $this->translations[$this->langcode]->getJobTitle();
    }

    public function setJobTitle(?string $title) : void
    {
        $this->translations[$this->langcode]->setJobTitle($title);
    }

    public function getResponsibility() : ?string
    {
        return $this->translations[$this->langcode]->getResponsibility();
    }

    public function setResponsibility(?string $responsibility) : void
    {
        $this->translations[$this->langcode]->setResponsibility($responsibility);
    }

    public function isEmailPublic() : bool
    {
        return $this->email_public == true;
    }

    public function setEmailPublic(bool $state) : void
    {
        $this->email_public = $state;
    }

    public function getEmail() : ?string
    {
        return $this->email;
    }

    public function setEmail(string $email) : void
    {
        $this->email = $email;
    }

    public function getPhone() : ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone) : void
    {
        $this->phone = $phone;
    }

    public function getLibrary() : ?Library
    {
        return $this->library;
    }

    public function setLibrary(Library $organisation) : void
    {
        if ($this->library != $organisation) {
            if ($this->library) {
                $this->library->getPersons()->removeElement($this);
            }
            $this->library = $organisation;
            $this->library->getPersons()->add($this);
        }
    }

    public function getQualities() : array
    {
        return $this->qualities;
    }

    public function setQualities(array $qualities) : void
    {
        $this->qualities = $qualities;
    }

    public function isHead() : bool
    {
        return $this->head == true;
    }

    public function setHead(bool $state) : void
    {
        $this->head = $state;
    }

    public function getUrl() : ?string
    {
      return $this->url;
    }

    public function setUrl(?string $url) : void
    {
        $this->url = $url;
    }
}

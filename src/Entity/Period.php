<?php

namespace App\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Feature\GroupOwnership;
use App\Entity\Feature\ModifiedAwareness;
use App\Entity\Feature\Translatable;
use App\I18n\Translations;

/**
 * @ORM\Entity
 * @ORM\Table(name="periods")
 */
class Period extends EntityBase implements GroupOwnership, ModifiedAwareness, Translatable
{
    use Feature\GroupOwnershipTrait;
    use Feature\ModifiedAwarenessTrait;
    use Feature\TranslatableTrait;

    /**
     * @ORM\Column(type="date")
     */
    private $valid_from;

    /**
     * @ORM\Column(type="date")
     */
    private $valid_until;

    /**
     * @ORM\Column(type="string")
     */
    private $section = 'default';

    /**
     * @ORM\Column(type="boolean")
     */
    private $shared = false;

    /**
     * @ORM\OneToMany(targetEntity="PeriodData", mappedBy="entity", orphanRemoval=true, cascade={"persist", "remove"}, fetch="EXTRA_LAZY", indexBy="langcode")
     */
    private $translations;

    /**
     * @ORM\ManyToOne(targetEntity="Library", inversedBy="periods")
     */
    private $library;

    /**
     * @ORM\ManyToOne(targetEntity="Department", inversedBy="periods")
     */
    private $department;

    /**
     * @ORM\Column(type="json_array")
     */
    private $days = [];

    public function __toString()
    {
        return $this->getName();
    }

    public function isActive(DateTimeInterface $datetime = null) : bool
    {
        if (!$datetime) {
            $datetime = new DateTime;
        }
        if ($this->valid_from > $datetime) {
            return false;
        }
        return !$this->isExpired($datetime);
    }

    public function isExpired(DateTimeInterface $datetime = null) : bool
    {
        if (!$datetime) {
            $datetime = new DateTime;
        }
        return $this->valid_until && $this->valid_until < $datetime;
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
        $this->translations[$this->langcode]->setDescription($name);
    }

    public function getDays() : array
    {
        return $this->days;
    }

    public function setDays(array $days) : void
    {
        $this->days = $days;
    }

    public function getSection() : string
    {
        trigger_error('Period::getSection() is deprecated', E_USER_DEPRECATED);
        return $this->section;
    }

    public function setSection(string $section) : void
    {
        trigger_error('Period::setSection() is deprecated.', E_USER_DEPRECATED);
        $this->section = $section;
    }

    public function getValidFrom() : DateTime
    {
        return $this->valid_from;
    }

    public function setValidFrom(DateTime $date) : void
    {
        $this->valid_from = $date;
    }

    public function getValidUntil() : ?DateTime
    {
        return $this->valid_until;
    }

    public function setValidUntil(?DateTime $date) : void
    {
        $this->valid_until = $date;
    }

    public function isContinuous() : bool
    {
        return is_null($this->valid_until);
    }

    public function setContinuous(bool $state) : void
    {
        $this->continuous = $state;
    }

    public function isShared() : bool
    {
        return $this->shared == true;
    }

    public function getLibrary() : ?Library
    {
        return $this->library;
    }

    public function setLibrary(Library $organisation) : void
    {
        $this->library = $organisation;
    }

    public function getDepartment() : ?Department
    {
        return $this->department;
    }

    public function setDepartment(?Department $department) : void
    {
        $this->department = $department;
    }

    public function getOrganisation()
    {
        debug_print_backtrace(2);
        exit('called');
    }
}

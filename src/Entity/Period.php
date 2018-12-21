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
 * @ORM\EntityListeners({"App\Doctrine\EventListener\ClearSchedulesOnPeriodRemove"})
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
     * NOTE: Required for migrating data but will be dropped after that.
     *
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
     * @ORM\ManyToOne(targetEntity="Facility", inversedBy="periods")
     */
    private $parent;

    /**
     * @ORM\ManyToOne(targetEntity="Department", inversedBy="periods")
     */
    private $department;

    /**
     * @ORM\Column(type="json_array")
     */
    private $days = [];

    /**
     * @ORM\Column(type="boolean")
     */
    private $is_legacy_format = false;

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
        if (!$this->valid_until) {
            return false;
        }

        $ref = ($datetime ?? new \DateTime)->format('Ymd');
        return $this->valid_until->format('Ymd') < $ref;
    }

    public function getName() : ?string
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
        return $this->days ?? [];
    }

    public function setDays(array $days) : void
    {
        $this->days = $days;
    }

    public function getSection() : ?string
    {
        // trigger_error('Period::getSection() is deprecated', E_USER_DEPRECATED);
        return $this->section;
    }

    public function setSection(string $section) : void
    {
        // trigger_error('Period::setSection() is deprecated.', E_USER_DEPRECATED);
        $this->section = $section;
    }

    public function getValidFrom() : ?DateTime
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

    public function getParent() : ?LibraryInterface
    {
        return $this->parent;
    }

    public function setParent(LibraryInterface $organisation) : void
    {
        $this->parent = $organisation;
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

    public function isLegacyFormat() : bool
    {
        return $this->is_legacy_format;
    }

    /*
     * Form actually sends a NULL value for FALSE but that's okay because this is a temporary field.
     */
    public function setIsLegacyFormat(?bool $state) : void
    {
        $this->is_legacy_format = (bool)$state;
    }

    public function getLibrary() : ?LibraryInterface
    {
        return $this->getParent();
    }

    public function setLibrary(LibraryInterface $library) : void
    {
        $this->setParent($library);
    }
}

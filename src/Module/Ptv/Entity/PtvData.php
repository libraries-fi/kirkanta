<?php

namespace App\Module\Ptv\Entity;

use DateTime;
use App\Entity\EntityBase;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="ptv_data")
 */
class PtvData extends EntityBase
{
    const DRAFT = 0;
    const PUBLISHED = 1;

    //
    protected $id;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $entity_id;

    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    private $entity_type;

    /**
     * @ORM\Column(type="string")
     */
    private $ptv_identifier;

    /**
     * @ORM\Column(type="boolean")
     */
    private $enabled = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $published = true;

    /**
     * @ORM\Column(type="datetime")
     */
    private $last_export;

    public function __construct(string $entity_type, int $entity_id)
    {
        parent::__construct();

        $this->entity_type = $entity_type;
        $this->entity_id = $entity_id;
    }

    public function getEntityId() : int
    {
        return $this->entity_id;
    }

    public function getEntityType() : string
    {
        return $this->entity_type;
    }

    public function getPtvIdentifier() : ?string
    {
        return $this->ptv_identifier;
    }

    public function setPtvIdentifier(string $id) : void
    {
        $this->ptv_identifier = $id;
    }

    public function isEnabled() : bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $state) : void
    {
        $this->enabled = $state;
    }

    public function isPublished() : bool
    {
        return $this->published;
    }

    public function setPublished(bool $state) : void
    {
        $this->published = $state;
    }

    public function getLastExport() : ?DateTime
    {
        return $this->last_export;
    }

    public function setLastExport(DateTime $time) : void
    {
        $this->last_export = $time;
    }
}

<?php

namespace App\Module\Finna\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\EntityDataBase;

/**
 * @ORM\Entity
 * @ORM\Table(name="finna_additions_data")
 */
class FinnaAdditionsData extends EntityDataBase
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="FinnaAdditions", inversedBy="translations")
     */
    protected $entity;

    /**
     * @ORM\Column(type="string")
     */
    private $usage_info;

    /**
     * @ORM\Column(type="string")
     */
    private $notification;

    public function getUsageInfo() : ?string
    {
        return $this->usage_info;
    }

    public function setUsageInfo(?string $text) : void
    {
        $this->usage_info = $text;
    }

    public function getNotification() : ?string
    {
        return $this->notification;
    }

    public function setNotification(?string $text) : void
    {
        $this->notification = $text;
    }

    public function getEntity() : FinnaAdditions
    {
        return $this->entity;
    }

    public function setEntity(FinnaAdditions $entity) : void
    {
        $this->entity = $entity;
        $this->id = $entity->getId();
    }
}

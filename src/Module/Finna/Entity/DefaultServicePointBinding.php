<?php

namespace App\Module\Finna\Entity;

use App\Entity\EntityBase;
use App\Entity\Library;
use App\Entity\LibraryInterface;
use App\Entity\ServicePoint;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

/**
 * @ORM\Entity
 * @ORM\Table(name="finna_service_point_bindings")
 */
class DefaultServicePointBinding
{
    /**
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="FinnaAdditions", inversedBy="service_point")
     */
    private $parent;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\ServicePoint")
     */
    private $service_point;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Library")
     */
    private $library;

    public function __construct(FinnaAdditions $parent, LibraryInterface $entity)
    {
        $this->setParent($parent);

        if ($entity) {
            $this->setTargetEntity($entity);
        }
    }

    public function __toString() : string
    {
        return (string)($this->library ?: $this->service_point);
    }

    public function getId() : int
    {
        return $this->getTargetEntity()->getId();
    }

    public function getName() : string
    {
        return $this->getTargetEntity()->getName();
    }

    public function getLibrary() : ?Library
    {
        return $this->library;
    }

    public function getServicePoint() : ?ServicePoint
    {
        return $this->service_point;
    }

    public function getTargetEntity() : LibraryInterface
    {
        return $this->library ?: $this->service_point;
    }

    public function setTargetEntity(LibraryInterface $entity) : void
    {
        if ($entity instanceof Library) {
            $this->library = $entity;
            $this->service_point = null;
        } elseif ($entity instanceof ServicePoint) {
            $this->service_point = $entity;
            $this->library = null;
        } else {
            $class = get_class($entity);
            throw new InvalidArgumentException("Unsupported entity of class {$class}");
        }
    }

    public function getParent() : FinnaAdditions
    {
        return $this->parent;
    }

    public function setParent(FinnaAdditions $parent) : void
    {
        $this->parent = $parent;
    }
}

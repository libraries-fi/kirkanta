<?php

namespace App\Module\ServiceTree\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\EntityBase;
use App\Entity\Feature;
use App\Entity\Feature\GroupOwnership;
use App\Entity\Feature\StateAwareness;
use App\Entity\Feature\Sticky;
use App\Entity\Service;

/**
 * @ORM\Entity
 * @ORM\Table(name="service_tree")
 */
class ServiceCategory extends EntityBase implements GroupOwnership, StateAwareness, Sticky
{
    use Feature\GroupOwnershipTrait;
    use Feature\StateAwarenessTrait;
    use Feature\StickyTrait;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="ServiceCategory", inversedBy="children")
     */
    private $parent;

    /**
     * Other categories below this one.
     *
     * @ORM\OneToMany(targetEntity="ServiceCategory", mappedBy="parent")
     */
    private $children;

    /**
     * Services added to this category.
     *
     * @ORM\OneToMany(targetEntity="ServiceItem", mappedBy="category")
     */
    private $items;

    public function __construct()
    {
        $this->sticky = false;
        $this->children = new ArrayCollection();
        $this->items = new ArrayCollection();
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function setName(string $name) : void
    {
        $this->name = $name;
    }

    public function getParent() : ?ServiceCategory
    {
        return $this->parent;
    }

    public function setParent(?ServiceCategory $parent) : void
    {
        $this->parent = $parent;
    }

    public function getChildren() : Collection
    {
        return $this->children;
    }

    public function getItems() : Collection
    {
        return $this->items;
    }

    public function getRoot() : ServiceCategory
    {
        return $this->getParent() ? $this->getParent()->getRoot() : $this;
    }

    public function addService(Service $service) : void
    {
        $this->items->add(new ServiceItem($service));
    }

    public function contains(ServiceItem $item) : bool
    {
        return $this->items->contains($item);
    }
}

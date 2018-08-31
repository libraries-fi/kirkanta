<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_groups")
 */
class UserGroup extends EntityBase
{
    const ADMIN = 'admin';

    /**
     * @ORM\Column(type="string", name="role_id")
     */
    private $name;

    /**
     * @ORM\Column(type="string")
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="UserGroup", inversedBy="children")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="UserGroup", mappedBy="parent")
     */
    private $children;

    /**
     * @ORM\Column(type="text_array")
     */
    private $roles;

    /**
     * @ORM\Column(type="integer")
     */
    private $max_group_admins = 3;

    public function __construct($name = null)
    {
        parent::__construct();
        $this->name = $role_id;
        $this->children = new ArrayCollection;
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function setName(string $name) : void
    {
        $this->name = $id;
    }

    public function getDescription() : ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description) : void
    {
        $this->description = $description;
    }

    public function getParent() : ?UserGroup
    {
        return $this->parent;
    }

    public function setParent(?UserGroup $group) : void
    {
        $this->parent = $group;
    }

    public function getChildren() : iterable
    {
        return $this->children;
    }

    public function getTree() : iterable
    {
        $tree = [$this];
        foreach ($this->children as $group) {
            $tree = array_merge($tree, $group->getTree());
        }
        for ($parent = $this->getParent(); $parent; $parent = $parent->getParent()) {
            $tree[] = $parent;
        }
        return $tree;
    }

    public function getRoles() : array
    {
        return $this->roles;
    }

    public function setRoles(array $roles) : void
    {
        $this->roles = $roles;
    }

    public function getRoot() : UserGroup
    {
        return $this->getParent() ? $this->getParent()->getRoot() : $this;
    }

    public function getMaxGroupAdmins() : int
    {
        return $this->max_group_admins;
    }

    public function setMaxGroupAdmins(int $limit) : void
    {
        $this->max_group_admins = $limit;
    }
}

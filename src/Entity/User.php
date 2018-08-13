<?php

namespace App\Entity;

use DateTime;
use Serializable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Feature\CreatedAwareness;
use UserAccountsBundle\BasicUserImplementation;
use UserAccountsBundle\UserInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 */
class User extends EntityBase implements CreatedAwareness, Serializable, UserInterface
{
    use BasicUserImplementation {
        getRoles as protected getOwnRoles;
    }

    use Feature\CreatedAwarenessTrait;

    /**
     * @ORM\ManyToOne(targetEntity="UserGroup")
     */
    protected $group;

    /**
     * @ORM\ManyToMany(targetEntity="Notification")
     * @ORM\JoinTable(name="users_read_notifications")
     */
    protected $read_notifications;

    private $cachedRoles;

    public function __construct()
    {
        parent::__construct();
        $this->roles = new ArrayCollection;
        $this->read_notifications = new ArrayCollection;
    }

    public function getLastLogin() : ?DateTime
    {
        return $this->last_login;
    }

    public function getAllImpliedRoles() : array
    {
        $roles = $this->getRoles();

        foreach ($this->getGroup()->getTree() as $group) {
            $roles = array_merge($roles, $group->getRoles());
        }

        return array_unique($roles);
    }

    public function getRoles($with_group_roles = false) : array
    {
        if ($with_group_roles) {
            $roles = $this->getOwnRoles();

            foreach ($this->getGroup()->getTree() as $group) {
                $roles = array_merge($roles, $group->getRoles());
            }

            return $roles;
        } else {
            return $this->getOwnRoles();
        }
    }

     public function getGroup() : UserGroup
    {
        return $this->group;
    }

    public function setGroup(UserGroup $group) : void
    {
        $this->group = $group;
    }

    public function getReadNotifications() : Collection
    {
        return $this->read_notifications;
    }
}

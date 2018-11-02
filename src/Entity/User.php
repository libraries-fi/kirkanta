<?php

namespace App\Entity;

use App\Entity\Feature\CreatedAwareness;
use App\Module\UserManagement\Validator\GroupManagerCount;
use DateTime;
use Serializable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use UserAccountsBundle\BasicUserImplementation;
use UserAccountsBundle\UserInterface;


/**
 * @ORM\Entity(repositoryClass="App\Doctrine\UserRepository")
 * @ORM\Table(name="users")
 */
class User extends EntityBase implements CreatedAwareness, Serializable, UserInterface
{
    use BasicUserImplementation {
        getRoles as protected getOwnRoles;
    }

    use Feature\CreatedAwarenessTrait;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $municipal_account = false;

    /**
     * @ORM\ManyToOne(targetEntity="UserGroup")
     */
    protected $group;

    /**
     * @ORM\ManyToMany(targetEntity="Notification")
     * @ORM\JoinTable(name="users_read_notifications")
     */
    protected $read_notifications;

    /**
     * @GroupManagerCount
     */
    private $group_manager;

    public function __construct()
    {
        parent::__construct();
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

    public function isMunicipalAccount() : bool
    {
        return $this->municipal_account;
    }

    public function isGroupManager() : bool
    {
        if (is_null($this->group_manager)) {
            $role_id = \UserAccountsBundle\UserInterface::GROUP_MANAGER_ROLE;
            $this->group_manager = in_array($role_id, $this->getRoles());
        }

        return $this->group_manager;
    }

    public function setGroupManager(bool $state) : void
    {
        $role_id = \UserAccountsBundle\UserInterface::GROUP_MANAGER_ROLE;
        $pos = array_search($role_id, $this->roles);

        if ($state && $pos === false) {
            $this->roles[] = $role_id;
        } elseif (!$state && $pos !== false) {
            unset($this->roles[$pos]);
        }

        $this->group_manager = $state;
    }
}

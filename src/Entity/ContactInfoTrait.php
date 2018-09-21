<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Provides additional features for contact info attached to Library entities.
 */
trait ContactInfoTrait
{
    protected $department;

    public function setDepartment(?Department $department) : void
    {
        $this->department = $department;

        if ($department) {
            $this->setLibrary($department->getLibrary());
        }
    }

    public function getDepartment() : ?Department
    {
        return $this->department;
    }

    public function setLibrary(Library $library) : void
    {
        $this->setParent($library);
    }

    public function getLibrary() : Library
    {
        return $this->getParent();
    }
}

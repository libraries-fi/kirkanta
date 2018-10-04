<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Provides additional features for contact info attached to LibraryInterface entities.
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

    public function setLibrary(LibraryInterface $library) : void
    {
        $this->setParent($library);
    }

    public function getLibrary() : LibraryInterface
    {
        return $this->getParent();
    }

    public function getParent() : LibraryInterface
    {
        return $this->parent;
    }

    public function setParent(LibraryInterface $library) : void
    {
        $this->parent = $library;
    }
}

<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class WebsiteLink extends ContactInfo
{
    /**
     * @ORM\ManyToOne(targetEntity="Library", inversedBy="links")
     */
    protected $library;

    public function getUrl() : string
    {
        return $this->getContact();
    }
}

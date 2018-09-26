<?php

namespace App\Module\Finna\Entity;

use App\Entity\ContactInfo;
use App\Entity\WebsiteLinkTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class FinnaOrganisationWebsiteLink extends ContactInfo
{
    use WebsiteLinkTrait;

    /**
     * @ORM\ManyToOne(targetEntity="FinnaAdditions", inversedBy="links")
     */
    private $finna_organisation;

    /**
     * @ORM\Column(type="string")
     */
    private $category;

    public function getFinnaOrganisation() : FinnaAdditions
    {
        return $this->finna_organisation;
    }

    public function setFinnaOrganisation(FinnaAdditions $organisation) : void
    {
        $this->finna_organisation = $organisation;
    }

    public function getCategory() : ?string
    {
        return $this->category;
    }

    public function setCategory(string $category) : void
    {
        $this->category = $category;
    }
}

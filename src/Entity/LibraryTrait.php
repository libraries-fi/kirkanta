<?php

namespace App\Entity;

use App\I18n\Translations;
use App\Module\ApiCache\Entity\Feature\ApiCacheableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Provides implementation of LibraryInterface.
 */
trait LibraryTrait
{
    use ApiCacheableTrait;

    /**
     * @ORM\Column(type="custom_data_collection")
     */
    private $custom_data;

    /**
     * @ORM\Column(type="string")
     */
    private $type;

    /**
     * @ORM\Column(type="string")
     */
    private $isil;

    /**
     * @ORM\Column(type="string")
     */
    private $identificator;

    /**
     * @ORM\Column(type="integer")
     */
    private $founded;

    /**
     * @ORM\Column(type="string")
     */
    private $buses;

    /**
     * @ORM\Column(type="string")
     */
    private $trains;

    /**
     * @ORM\Column(type="string")
     */
    private $trams;

    /**
     * @ORM\Column(type="string")
     */
    private $building_architect;

    /**
     * @ORM\Column(type="string")
     */
    private $interior_designer;

    /**
     * @ORM\Column(type="integer")
     */
    private $construction_year;

    /**
     * @ORM\OneToOne(targetEntity="Address", cascade={"remove", "persist"}, orphanRemoval=true)
     */
    private $address;

    /**
     * @ORM\OneToOne(targetEntity="Address", cascade={"remove", "persist"}, orphanRemoval=true)
     */
    private $mail_address;

    /**
     * @ORM\ManyToOne(targetEntity="City", inversedBy="libraries")
     */
    private $city;

    /**
     * @ORM\ManyToOne(targetEntity="Consortium", inversedBy="libraries", fetch="LAZY")
     */
    private $consortium;

    /**
     * NOTE: Avoid persisting this value without an explicit DQL query because as of now we can't
     * define the parser mode used by to_tsvector in this column definition...
     *
     * ALWAYS use 'simple' mode with DQL.
     *
     * @ORM\Column(type="tsvector")
     */
    private $api_keywords;

    public function __construct()
    {
        parent::__construct();

        $this->accessibility = new ArrayCollection;
        $this->mobile_stops = new ArrayCollection;
        $this->periods = new ArrayCollection;
        $this->phone_numbers = new ArrayCollection;
        $this->pictures = new ArrayCollection;
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function getName() : string
    {
        return $this->translations[$this->langcode]->getName();
    }

    public function setName(string $name) : void
    {
        $this->translations[$this->langcode]->setName($name);
    }

    public function getShortName() : ?string
    {
        return $this->translations[$this->langcode]->getShortName();
    }

    public function setShortName(?string $name) : void
    {
        $this->translations[$this->langcode]->setShortName($name);
    }

    public function getType() : string
    {
        return $this->type;
    }

    public function setType(string $type) : void
    {
        $this->type = $type;
    }

    public function getIsil() : ?string
    {
        return $this->isil;
    }

    public function setIsil(?string $isil) : void
    {
        $this->isil = $isil;
    }

    public function getIdentificator() : ?string
    {
        return $this->identificator;
    }

    public function setIdentificator(?string $identificator) : void
    {
        $this->identificator = $identificator;
    }

    public function getSlogan() : ?string
    {
        return $this->translations[$this->langcode]->getSlogan();
    }

    public function setSlogan(?string $slogan) : void
    {
        $this->translations[$this->langcode]->setSlogan($slogan);
    }

    public function getDescription() : ?string
    {
        return $this->translations[$this->langcode]->getDescription();
    }

    public function setDescription(?string $description) : void
    {
        $this->translations[$this->langcode]->setDescription($description);
    }

    public function getBuses() : ?string
    {
        return $this->buses;
    }

    public function setBuses(?string $info) : void
    {
        $this->buses = $info;
    }

    public function getTrains() : ?string
    {
        return $this->trains;
    }

    public function setTrains(?string $info) : void
    {
        $this->trains = $info;
    }

    public function getTrams() : ?string
    {
        return $this->trams;
    }

    public function setTrams(?string $info) : void
    {
        $this->trams = $info;
    }

    public function getTransitDirections() : ?string
    {
        return $this->translations[$this->langcode]->getTransitDirections();
    }

    public function setTransitDirections(?string $info) : void
    {
        $this->translations[$this->langcode]->setTransitDirections($info);
    }

    public function getParkingInstructions() : ?string
    {
        return $this->translations[$this->langcode]->getParkingInstructions();
    }

    public function setParkingInstructions(?string $info) : void
    {
        $this->translations[$this->langcode]->setParkingInstructions($info);
    }

    public function getEmail() : string
    {
        return $this->translations[$this->langcode]->getEmail();
    }

    public function setEmail(string $email) : void
    {
        $this->translations[$this->langcode]->setEmail($email);
    }

    public function getHomepage() : ?string
    {
        return $this->translations[$this->langcode]->getHomepage();
    }

    public function setHomepage(?string $homepage) : void
    {
        $this->translations[$this->langcode]->setHomepage($homepage);
    }

    public function getConstructionYear() : ?int
    {
        return $this->construction_year;
    }

    public function setConstructionYear(?int $year) : void
    {
        $this->construction_year = $year;
    }

    public function getBuildingName() : ?string
    {
        return $this->translations[$this->langcode]->getBuildingName();
    }

    public function setBuildingName(?string $name) : void
    {
        $this->translations[$this->langcode]->setBuildingName($name);
    }

    public function getBuildingArchitect() : ?string
    {
        return $this->building_architect;
    }

    public function setBuildingArchitect(?string $architect) : void
    {
        $this->building_architect = $architect;
    }

    public function getInteriorDesigner() : ?string
    {
        return $this->interior_designer;
    }

    public function setInteriorDesigner(?string $designer) : void
    {
        $this->interior_designer = $designer;
    }

    public function getAddress() : ?Address
    {
        return $this->address;
    }

    public function setAddress(Address $address) : void
    {
        $this->address = $address;
        $this->setCity($address->getCity());
    }

    public function getMailAddress() : ?Address
    {
        return $this->mail_address;
    }

    public function setMailAddress(?Address $address) : void
    {
        $this->mail_address = $address;
    }

    public function getCity() : City
    {
        if (!$this->city && $this->address) {
            $this->city = $this->address->getCity();
        }

        return $this->city;
    }

    public function setCity(City $city) : void
    {
        $this->city = $city;

        if ($address = $this->getAddress()) {
            $address->setCity($city);
        }
    }

    public function getConsortium() : ?Consortium
    {
        return $this->consortium;
    }

    public function setConsortium(?Consortium $consortium) : void
    {
        $this->consortium = $consortium;
    }

    public function getPeriods() : Collection
    {
        return $this->periods;
    }

    public function getPictures() : Collection
    {
        return $this->pictures;
    }

    public function getPhoneNumbers() : Collection
    {
        return $this->phone_numbers;
    }

    public function getEmailAddresses() : Collection
    {
        return $this->email_addresses;
    }

    public function getLinks() : Collection
    {
        return $this->links;
    }

    public function getCustomData() : array
    {
        return $this->custom_data;
    }

    public function setCustomData(array $entries) : void
    {
        $this->custom_data = $entries ?: null;
    }

    public function getApiKeywords() : array
    {
        $document = $this->getApiDocument();
        $keywords = self::extractApiKeywords($document, ['name'], ['shortName'], ['slug'], ['address', 'city'], ['address', 'area'], ['address', 'zipcode']);

        if (isset($document['services'])) {
          $k2 = self::extractApiKeywordsArray($document['services'], ['name'], ['standardName']);
          $keywords = array_merge($keywords, $k2);
        }

        return $keywords;
    }
}

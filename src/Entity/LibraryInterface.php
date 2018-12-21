<?php

namespace App\Entity;

use App\Module\ApiCache\Entity\Feature\ApiCacheable;
use Doctrine\Common\Collections\Collection;

/**
 * Common interface for libraries and service points.
 */
interface LibraryInterface extends ApiCacheable
{

  public function getName() : string;
  public function setName(string $name) : void;

  public function getShortName() : ?string;
  public function setShortName(?string $name) : void;

  public function getType() : ?string;
  public function setType(string $type) : void;

  public function getIsil() : ?string;
  public function setIsil(?string $isil) : void;

  public function getIdentificator() : ?string;
  public function setIdentificator(?string $identificator) : void;

  public function getSlogan() : ?string;
  public function setSlogan(?string $slogan) : void;

  public function getDescription() : ?string;
  public function setDescription(?string $description) : void;

  public function getBuses() : ?string;
  public function setBuses(?string $info) : void;

  public function getTrains() : ?string;
  public function setTrains(?string $info) : void;

  public function getTrams() : ?string;
  public function setTrams(?string $info) : void;

  public function getTransitDirections() : ?string;
  public function setTransitDirections(?string $info) : void;

  public function getParkingInstructions() : ?string;
  public function setParkingInstructions(?string $info) : void;

  public function getEmail() : string;
  public function setEmail(string $email) : void;

  public function getHomepage() : ?string;
  public function setHomepage(?string $homepage) : void;

  public function getConstructionYear() : ?int;
  public function setConstructionYear(?int $year) : void;

  public function getBuildingName() : ?string;
  public function setBuildingName(?string $name) : void;

  public function getBuildingArchitect() : ?string;
  public function setBuildingArchitect(?string $architect) : void;

  public function getInteriorDesigner() : ?string;
  public function setInteriorDesigner(?string $designer) : void;

  public function getAddress() : ?Address;
  public function setAddress(Address $address) : void;

  public function getMailAddress() : ?Address;
  public function setMailAddress(?Address $address) : void;

  public function getCity() : City;
  public function setCity(City $city) : void;

  public function getConsortium() : ?Consortium;
  public function setConsortium(?Consortium $consortium) : void;

  public function getPeriods() : Collection;
  public function getPictures() : Collection;
  public function getPhoneNumbers() : Collection;
  public function getEmailAddresses() : Collection;
  public function getLinks() : Collection;

  public function getCustomData() : array;
  public function setCustomData(array $entries) : void;
}

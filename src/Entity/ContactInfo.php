<?php

namespace App\Entity;

use App\Entity\Feature\Translatable;
use App\Entity\Feature\Weight;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * NOTE: In order to implement two-level discriminator mapping, we're using a read-write view as
 * a source for Doctrine (as per the Table annotation).
 *
 * @ORM\Entity
 * @ORM\Table(name="contact_info_doctrine")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "library:email" = "EmailAddress",
 *     "library:phone" = "PhoneNumber",
 *     "library:website" = "WebsiteLink",
 *     "foreign:email" = "ServicePointEmailAddress",
 *     "foreign:phone" = "ServicePointPhoneNumber",
 *     "foreign:website" = "ServicePointWebsiteLink",
 * })
 */
abstract class ContactInfo extends EntityBase implements Translatable, Weight
{
    use Feature\TranslatableTrait;
    use Feature\WeightTrait;

    /**
     * NOTE: Since this entity is configured to read from a view, we must override the ID generator
     * to use underlying table's sequence.
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="contact_info_id_seq")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    private $contact;

    /**
     * @ORM\OneToMany(targetEntity="ContactInfoData", mappedBy="entity", orphanRemoval=true, cascade={"persist", "remove"}, fetch="EXTRA_LAZY", indexBy="langcode")
     * @Assert\Valid
     */
    private $translations;

    public function getContact() : string
    {
        return $this->contact;
    }

    public function setContact(string $contact) : void
    {
        $this->contact = $contact;
    }

    public function getName() : string
    {
        return $this->translations[$this->langcode]->getName();
    }

    public function setName($name) : void
    {
        $this->translations[$this->langcode]->setName($name);
    }

    public function getDescription() : ?string
    {
        return $this->translations[$this->langcode]->getDescription();
    }

    public function setDescription(?string $description) : void
    {
        $this->translations[$this->langcode]->setDescription($description);
    }

    public function getParent() : Facility
    {
        return $this->parent;
    }

    public function setParent(Facility $facility) : void
    {
        $this->parent = $facility;
    }

    public function getTranslations() : Collection
    {
        return $this->translations;
    }
}

<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity
 */
class ContactInfoData extends EntityDataBase
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="ContactInfo", inversedBy="translations")
     */
    protected $entity;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\Column(type="string")
     */
    private $description;

    public function getName() : string
    {
        return $this->name;
    }

    public function setName(string $name) : void
    {
        $this->name = $name;
    }

    public function getDescription() : ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description) : void
    {
        $this->description = $description;
    }

    public function getEntity() : ContactInfo
    {
        return $this->entity;
    }

    public function setEntity(ContactInfo $entity) : void
    {
        $this->entity = $entity;
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload) : void
    {
        if (!preg_match('/^[\w\d\s\-\(\)\.,\/:]+$/u', $this->getName())) {
            $context->buildViolation('Only the following characters are allowed: a-z 0-9 ( ) . , / and space.')
                ->atPath('name')
                ->addViolation();
        }
    }
}

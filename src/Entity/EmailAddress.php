<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class EmailAddress extends ContactInfo
{
    use ContactInfoTrait;
    use EmailAddressTrait;
}

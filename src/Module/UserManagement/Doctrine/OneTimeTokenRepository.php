<?php

namespace App\Module\UserManagement\Doctrine;

use App\Module\UserManagement\Entity\OneTimeToken;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\PropertyAccess\PropertyAccess;

class OneTimeTokenRepository extends EntityRepository
{
    private $encoder;

    public function createToken(string $nonce, string $purpose) : OneTimeToken
    {
        return new OneTimeToken($this->hash($nonce), $purpose);
    }

    public function findToken(string $nonce, string $purpose) : OneTimeToken
    {
        return $this->findOneBy([
            'token' => $this->hash($nonce),
            'purpose' => $purpose,
        ]);
    }

    public function eraseToken(OneTimeToken $token) : void
    {
        $em = $this->getEntityManager();
        $em->remove($token);
        $em->flush($token);
    }

    private function hash(string $nonce) : string
    {
        return hash('sha256', $nonce);
    }
}

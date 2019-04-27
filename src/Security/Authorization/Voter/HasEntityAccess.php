<?php

namespace App\Security\Authorization\Voter;

use OutOfBoundsException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use App\EntityTypeManager;
use App\Entity;

class HasEntityAccess extends Voter
{
    private $access;
    private $types;

    public function __construct(AuthorizationCheckerInterface $access_check, EntityTypeManager $types)
    {
        $this->access = $access_check;
        $this->types = $types;
    }

    protected function supports($attribute, $subject) : bool
    {
        return strpos($attribute, 'ENTITY_ACCESS:') === 0;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if ($subject instanceof Request) {
            try {
                $entity_type = $subject->attributes->get('entity_type');
                $class = $this->types->getEntityClass($entity_type);
                $id = $subject->attributes->getInt($entity_type);

                if (!$this->access->isGranted('VIEW', $class) == self::ACCESS_GRANTED) {
                    return self::ACCESS_DENIED;
                }

                if ($id) {
                    $entity = $this->types->getRepository($type)->findOneById($id);

                    var_dump($entity->getGroup());

                    var_dump($token->getRoles());
                    exit;
                } else {
                    return self::ACCESS_GRANTED;
                }

                exit('sad');
            } catch (OutOfBoundsException $e) {
            }
        }
        exit('vote on entity access');

        return self::ACCESS_ABSTAIN;
    }
}

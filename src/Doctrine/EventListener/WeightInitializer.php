<?php

namespace App\Doctrine\EventListener;

use App\Entity\Feature\Weight;
use App\Module\Finna\Entity\FinnaOrganisationWebsiteLink;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class WeightInitializer implements EventSubscriber
{
    public function getSubscribedEvents() : array
    {
        return [Events::prePersist, Events::preRemove];
    }

    public function prePersist(LifecycleEventArgs $args) : void
    {
        $entity = $args->getEntity();

        if ($entity instanceof FinnaOrganisationWebsiteLink) {
            if (is_null($entity->getWeight())) {
                try {
                    $weight = $args->getEntityManager()->createQueryBuilder()
                        ->select('e.weight')
                        ->from(FinnaOrganisationWebsiteLink::class, 'e')
                        ->where('e.finna_organisation = :library')
                        ->setParameter('library', $entity->getFinnaOrganisation())
                        ->orderBy('e.weight', 'desc')
                        ->setMaxResults(1)
                        ->getQuery()
                        ->getSingleScalarResult();
                } catch (\Doctrine\ORM\NoResultException $e) {
                    $weight = -1;
                }

                $entity->setWeight($weight + 1);
            }
        } elseif ($entity instanceof Weight) {
            if (is_null($entity->getWeight())) {
                try {
                    $weight = $args->getEntityManager()->createQueryBuilder()
                        ->select('e.weight')
                        ->from(get_class($entity), 'e')
                        ->where('e.parent = :library')
                        ->setParameter('library', $entity->getParent())
                        ->orderBy('e.weight', 'desc')
                        ->setMaxResults(1)
                        ->getQuery()
                        ->getSingleScalarResult();
                } catch (\Doctrine\ORM\NoResultException $e) {
                    $weight = -1;
                }

                $entity->setWeight($weight + 1);
            } else {
                if (!$entity->getId()) {
                    /**
                     * The only use-case with newly created entities is to force them to
                     * the top of the list. Therefore we can set a temporary weight to make sure
                     * that this entity will be made first. The internal sorting algorithm does
                     * not work properly with NULL IDs but this hack fixes it.
                     */
                    $entity->setWeight(-1);
                }

                $collection = $this->loadRelatedEntities($args);
                $collection->set(0, $entity);
                $this->getRepository($args)->updateWeights($collection);
            }
        }
    }

    public function preRemove(LifecycleEventArgs $args) : void
    {
        $entity = $args->getEntity();

        if ($entity instanceof Weight) {
            $collection = $this->loadRelatedEntities($args);
            $collection->remove($args->getEntity()->getId());
            $this->getRepository($args)->updateWeights($collection);
        }
    }

    private function getRepository(LifecycleEventArgs $args)
    {
        return $args->getEntityManager()->getRepository(get_class($args->getEntity()));
    }

    private function loadRelatedEntities(LifecycleEventArgs $args) : ArrayCollection
    {
        $entity = $args->getEntity();
        $builder = $args->getEntityManager()->createQueryBuilder()
            ->select('e')
            ->from(get_class($entity), 'e');
        
        // Parent relation changes here because of the FinnaOrganisationWebsiteLink
        // different column (finna_organisation) instead of what other ContactInfo
        // based entitities are using (parent).
        if ($entity instanceof FinnaOrganisationWebsiteLink) {
            $builder->where('e.finna_organisation = :organisation')
                ->setParameter('organisation', $entity->getFinnaOrganisation());
        } else {
            $builder->where('e.parent = :library')
                ->setParameter('library', $entity->getParent());
        }
        
        $builder->orderBy('e.weight', 'desc')
                ->indexBy('e', 'e.id');

        $entities = $builder->getQuery()->getResult();
        
        return new ArrayCollection($entities);
    }
}

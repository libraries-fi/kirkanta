<?php

namespace App\Doctrine;

use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ConsortiumRepository extends EntityRepository
{
    /**
     * Used on LibraryForm to populate consortium combobox options.
     *
     * Intention is to filter out regular municipal consortiums as associations
     * to them are managed automatically for municipal libraries, and the assumption
     * is that these consortiums WILL NOT be used with non-municipal libraries at all.
     */
    public function createNonMunicipalConsortiumsQueryBuilder(string $langcode = 'fi') : QueryBuilder
    {
        $cids = $this->getEntityManager()
            ->getRepository('App:City')
            ->createQueryBuilder('c')
            ->select('IDENTITY(c.consortium)')
            ->distinct()
            ->andWhere('c.consortium IS NOT NULL')
            ->getQuery()
            ->getResult()
            ;

        return $this->createQueryBuilder('c')
            ->addSelect('d')
            ->join('c.translations', 'd', 'WITH', 'd.langcode = :langcode')
            ->andWhere('c.id NOT IN (:ids)')
            ->setParameter('ids', $cids)
            ->setParameter('langcode', $langcode)
            ->orderBy('d.name')
            ;
    }
}

<?php

namespace App\Repository;

use App\Entity\Sortie;
use App\Entity\SortieFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    /**
    * @return Sortie[] Returns an array of Sortie objects
    */

    public function filter(SortieFilter $filter)
    {
        $query = $this
            ->createQueryBuilder('s')
            ->where('s.campusOrganisateur = :campus')
            ->setParameter('campus', $filter->getCampus()->getId())
        ;

        if (!empty($filter->getQuery())) {
            $query = $query
                ->andWhere('s.nom LIKE :q')
                ->setParameter('q', "%{$filter->getQuery()}%")
            ;
        }

        if ($filter->getStartDate() != null) {
            $query = $query
                ->andWhere('s.dateHeureDebut > :startDate')
                ->setParameter('startDate',$filter->getStartDate())
            ;
        }

        if ($filter->getEndDate() != null) {
            $query = $query
                ->andWhere('s.dateHeureDebut < :endDate')
                ->setParameter('endDate',$filter->getEndDate())
            ;
        }

        if ($filter->isOrganisateur()) {

            // TODO Implémenter filtre 'dont je suis l'organisateur'

        }

        if ($filter->isInscrit()) {

            // TODO Implémenter filtre 'dont je suis inscrit'

        }

        if ($filter->isNotInscrit()) {

            // TODO Implémenter filtre 'dont je ne suis pas inscrit'

        }

        if ($filter->isPassed()) {

            // TODO Implémenter filtre 'sortie passée'

        }

        return $query
            ->getQuery()
            ->getResult()
        ;
    }


    /*
    public function findOneBySomeField($value): ?Sortie
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

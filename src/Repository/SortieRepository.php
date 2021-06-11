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
            ->leftJoin('s.participants', 'p')
            ->leftJoin('s.etat', 'e')
            ->andWhere('e.libelle NOT LIKE :libelle')
            ->setParameter('libelle', 'Créée')
            ->addSelect('e')
            ->join('s.organisateur', 'o')
            ->addSelect('o')
            ->addSelect('p')
        ;

        if (!empty($filter->getQuery())) {
            $query = $query
                ->andWhere('s.nom LIKE :q')
                ->setParameter('q', '%'.$filter->getQuery().'%')
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

            $query = $query
                ->orWhere('s.organisateur = :user')
                ->setParameter('user', $filter->getUser())
                ;

        } else {

            $query = $query
                ->andWhere('s.organisateur != :user')
                ->setParameter('user', $filter->getUser())
            ;

        }


        if ($filter->isInscrit() && !$filter->isNotInscrit()) {

            $query = $query
                ->andWhere('p = :user')
                ->setParameter('user', $filter->getUser())
            ;

        } elseif ($filter->isNotInscrit() && !$filter->isInscrit()) {

            $query = $query
                ->andWhere('p IS NULL OR p <> :user')
                ->setParameter('user', $filter->getUser())
            ;

        }

        if ($filter->isPassed()) {

            $query = $query
                ->andWhere('s.dateHeureDebut < :date')
                ->setParameter('date', new \DateTime())
            ;

        } else {
            $query = $query
                ->andWhere('s.dateHeureDebut >= :date')
                ->setParameter('date', new \DateTime())
            ;
        }

        return $query
            ->addOrderBy('s.dateHeureDebut')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Sortie[] Returns an array of Sortie objects
     */
    public function sortiesExpired() {
        $query = $this
            ->createQueryBuilder('s')
            ->where('s.dateHeureDebut < :date')
            ->setParameter('date', new \DateTime())
        ;

        return $query
            ->getQuery()
            ->getResult();
    }
    /**
     * @return Sortie[] Returns an array of Sortie objects
     */
    public function closedInscriptions() {
        $query = $this
            ->createQueryBuilder('s')
            ->leftJoin('s.etat', 'e')
            ->where('s.dateLimiteInscription <= :date')
            ->setParameter('date', new \DateTime())
            ->andWhere('e.libelle = :libelle')
            ->setParameter('libelle', 'Ouverte')
        ;

        return $query
            ->getQuery()
            ->getResult();
    }
    /**
     * @return Sortie[]|null Returns an array of Sortie objects
     */
    public function inProgress() {
        $today = new \DateTime();

        return $this
            ->createQueryBuilder('s')
            ->leftJoin('s.etat', 'e')
            ->where('s.dateHeureDebut <= :date')
            ->setParameter('date', $today)
            ->andWhere('e.libelle = :ouverte OR e.libelle = :cloturee')
            ->setParameter('ouverte', 'Ouverte')
            ->setParameter('cloturee', 'Clôturée')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Sortie[]|null Returns an array of Sortie objects
     */
    public function expired() {
        $today = new \DateTime();
        $interval = new \DateInterval('P1M');
        $date = date_sub($today, $interval);

        return $this
            ->createQueryBuilder('s')
            ->where('s.dateHeureDebut <= :date')
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult();
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

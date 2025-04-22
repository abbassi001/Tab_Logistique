<?php

namespace App\Repository;

use App\Entity\Expediteur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Expediteur>
 */
class ExpediteurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Expediteur::class);
    }

//    /**
//     * @return Expediteur[] Returns an array of Expediteur objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Expediteur
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

// Ajouter ces méthodes à votre ExpediteurRepository.php existant

/**
 * Compte le nombre d'expéditeurs créés dans les X derniers jours
 */
public function countRecentExpediteurs(int $days): int
{
    // Note: Cette méthode assume que vous avez un champ date_creation dans votre entité Expediteur
    // Si ce n'est pas le cas, vous devrez l'adapter à votre modèle
    
    $date = new \DateTime();
    $date->modify('-' . $days . ' days');

    return $this->createQueryBuilder('d')
    ->select('COUNT(d.id)')
    ->getQuery()
    ->getSingleScalarResult();
}

// Ajouter ces méthodes à votre DestinataireRepository.php existant

}

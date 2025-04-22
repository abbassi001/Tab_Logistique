<?php

namespace App\Repository;

use App\Entity\Destinataire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Destinataire>
 */
class DestinataireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Destinataire::class);
    }

//    /**
//     * @return Destinataire[] Returns an array of Destinataire objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Destinataire
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }


/**
 * Compte le nombre de destinataires créés dans les X derniers jours
 */
public function countRecentDestinataires(int $days): int
{
    // Note: Cette méthode assume que vous avez un champ date_creation dans votre entité Destinataire
    // Si ce n'est pas le cas, vous devrez l'adapter à votre modèle
    
    $date = new \DateTime();
    $date->modify('-' . $days . ' days');

    return $this->createQueryBuilder('d')
        ->select('COUNT(d.id)')
        ->getQuery()
        ->getSingleScalarResult();
}
}

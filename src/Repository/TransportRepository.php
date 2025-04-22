<?php

namespace App\Repository;

use App\Entity\Transport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Transport>
 */
class TransportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transport::class);
    }

//    /**
//     * @return Transport[] Returns an array of Transport objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Transport
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }


// Ajouter ces méthodes à votre TransportRepository.php existant

/**
 * Compte le nombre de transports par type
 */
public function countByType(): array
{
    $conn = $this->getEntityManager()->getConnection();
    $sql = '
        SELECT t.type_transport as type, COUNT(t.id) as count
        FROM transport t
        GROUP BY t.type_transport
        ORDER BY count DESC
    ';
    
    $stmt = $conn->prepare($sql);
    $resultSet = $stmt->executeQuery();
    $results = $resultSet->fetchAllAssociative();
    
    $counts = [];
    foreach ($results as $result) {
        $counts[$result['type']] = (int)$result['count'];
    }
    
    return $counts;
}

/**
 * Compte le nombre de transports par compagnie
 */
public function countByCompany(): array
{
    $conn = $this->getEntityManager()->getConnection();
    $sql = '
        SELECT t.compagnie_transport as company, COUNT(t.id) as count
        FROM transport t
        GROUP BY t.compagnie_transport
        ORDER BY count DESC
        LIMIT 5
    ';
    
    $stmt = $conn->prepare($sql);
    $resultSet = $stmt->executeQuery();
    $results = $resultSet->fetchAllAssociative();
    
    $counts = [];
    foreach ($results as $result) {
        $counts[$result['company']] = (int)$result['count'];
    }
    
    return $counts;
}
}

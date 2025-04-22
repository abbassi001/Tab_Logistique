<?php

namespace App\Repository;

use App\Entity\Warehouse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;

/**
 * @extends ServiceEntityRepository<Warehouse>
 */
class WarehouseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Warehouse::class);
    }

    /**
     * Enregistre un entrepôt en base de données
     */
    public function save(Warehouse $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Supprime un entrepôt de la base de données
     */
    public function remove(Warehouse $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Trouve tous les entrepôts actifs
     * 
     * @return Warehouse[]
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('w')
            ->orderBy('w.nomEntreprise', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche d'entrepôts par terme de recherche
     */
    public function findBySearchTerm(string $term): array
    {
        return $this->createQueryBuilder('w')
            ->andWhere('
                w.codeUt LIKE :term OR 
                w.nomEntreprise LIKE :term OR 
                w.adresseWarehouse LIKE :term OR 
                w.ville LIKE :term
            ')
            ->setParameter('term', '%' . $term . '%')
            ->orderBy('w.nomEntreprise', 'ASC')
            ->getQuery()
            ->getResult();
    }
    
    /**
     * Récupère les entrepôts avec le plus de colis
     */
    public function findTopWarehouses(int $limit = 5): array
    {
        return $this->createQueryBuilder('w')
            ->select('w, COUNT(c.id) as colisCount')
            ->leftJoin('App\Entity\Colis', 'c', Join::WITH, 'c.warehouse = w')
            ->groupBy('w.id')
            ->orderBy('colisCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
    
    /**
     * Calcule le taux de performance d'un entrepôt
     * (pourcentage de colis livrés par rapport au total)
     */
    public function calculatePerformanceRate(int $warehouseId): float
    {
        $conn = $this->getEntityManager()->getConnection();
        
        // Compter le nombre total de colis pour cet entrepôt
        $totalSql = '
            SELECT COUNT(c.id) as total
            FROM colis c
            WHERE c.warehouse_id = :warehouseId
        ';
        
        $totalStmt = $conn->prepare($totalSql);
        $totalResult = $totalStmt->executeQuery(['warehouseId' => $warehouseId]);
        $total = $totalResult->fetchOne();
        
        if ($total == 0) {
            return 0; // Éviter division par zéro
        }
        
        // Compter le nombre de colis livrés pour cet entrepôt
        $deliveredSql = '
            SELECT COUNT(DISTINCT c.id) as delivered
            FROM colis c
            JOIN statut s ON s.colis_id = c.id
            WHERE c.warehouse_id = :warehouseId
            AND s.type_statut = :deliveredStatus
        ';
        
        $deliveredStmt = $conn->prepare($deliveredSql);
        $deliveredResult = $deliveredStmt->executeQuery([
            'warehouseId' => $warehouseId,
            'deliveredStatus' => 'LIVRE' // Utiliser la valeur de votre enum StatusType::LIVRE
        ]);
        $delivered = $deliveredResult->fetchOne();
        
        // Calculer le taux de performance
        return ($delivered / $total) * 100;
    }


/**
 * Get top warehouses with activity metrics
 *
 * @param int $limit Maximum number of warehouses to return
 * @return array Warehouses with performance data
 */
public function getTopWarehouses(int $limit = 5): array
{
    $qb = $this->createQueryBuilder('w')
        ->select('w.id, w.codeUt, w.nomEntreprise')
        ->addSelect('COUNT(c.id) as activity')
        ->leftJoin('App\Entity\Colis', 'c', 'WITH', 'c.warehouse = w.id')
        ->groupBy('w.id')
        ->orderBy('activity', 'DESC')
        ->setMaxResults($limit);
    
    $result = $qb->getQuery()->getResult();
    
    // Calculate performance for each warehouse
    foreach ($result as &$warehouse) {
        $warehouse['performance'] = $this->calculateWarehousePerformance($warehouse['id']);
    }
    
    return $result;
}

/**
 * Calculate warehouse performance metric
 *
 * @param int $warehouseId Warehouse ID
 * @return float Performance percentage
 */
public function calculateWarehousePerformance(int $warehouseId): float
{
    $conn = $this->getEntityManager()->getConnection();
    
    // Count total colis for this warehouse
    $totalSQL = "SELECT COUNT(id) as total FROM colis WHERE warehouse_id = :warehouseId";
    $totalResult = $conn->executeQuery($totalSQL, ['warehouseId' => $warehouseId])->fetchAssociative();
    $total = (int)$totalResult['total'];
    
    if ($total === 0) {
        return 0;
    }
    
    // Count delivered colis for this warehouse
    $deliveredSQL = "SELECT COUNT(DISTINCT c.id) as delivered 
                   FROM colis c
                   JOIN statut s ON s.colis_id = c.id
                   WHERE c.warehouse_id = :warehouseId AND s.type_statut = 'LIVRE'";
    
    $deliveredResult = $conn->executeQuery($deliveredSQL, ['warehouseId' => $warehouseId])->fetchAssociative();
    $delivered = (int)$deliveredResult['delivered'];
    
    // Calculate percentage
    return round(($delivered / $total) * 100);
}
}
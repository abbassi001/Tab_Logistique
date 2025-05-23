<?php

namespace App\Repository;

use App\Entity\Colis;
use App\Entity\Warehouse;
use App\Enum\StatusType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\Query\Expr\Join;

/**
 * @extends ServiceEntityRepository<Colis>
 */
class ColisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Colis::class);
    }
    
    /**
     * Trouve tous les colis avec pagination, filtres et tri
     *
     * @param int $page Le numéro de page
     * @param int $limit Nombre d'éléments par page
     * @param array $filters Filtres (codeTracking, dateMin, dateMax, expediteur, destinataire...)
     * @param array $order Ordre de tri (champ, direction)
     * @return array
     */
    public function findAllPaginated(int $page = 1, int $limit = 10, array $filters = [], array $order = ['id' => 'DESC']): array
    {
        $query = $this->createQueryBuilder('c')
            ->leftJoin('c.expediteur', 'e')
            ->leftJoin('c.destinataire', 'd')
            ->leftJoin('c.statuts', 's', 'WITH', 's.id = (
                SELECT MAX(s2.id) FROM App\Entity\Statut s2 WHERE s2.colis = c
            )')
            ->addSelect('e', 'd', 's') // Optimise les requêtes avec des jointures
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);
        
        // Appliquer les filtres
        if (!empty($filters['search'])) {
            $query->andWhere('c.codeTracking LIKE :search OR d.nom_entreprise_individu LIKE :search OR e.nom_entreprise_individu LIKE :search')
                ->setParameter('search', '%' . $filters['search'] . '%');
        }
        
        if (!empty($filters['dateMin'])) {
            $query->andWhere('c.date_creation >= :dateMin')
                ->setParameter('dateMin', new \DateTime($filters['dateMin']));
        }
        
        if (!empty($filters['dateMax'])) {
            $query->andWhere('c.date_creation <= :dateMax')
                ->setParameter('dateMax', new \DateTime($filters['dateMax']));
        }
        
        if (!empty($filters['expediteur'])) {
            $query->andWhere('e.id = :expediteur')
                ->setParameter('expediteur', $filters['expediteur']);
        }
        
        if (!empty($filters['destinataire'])) {
            $query->andWhere('d.id = :destinataire')
                ->setParameter('destinataire', $filters['destinataire']);
        }
        
        if (!empty($filters['status'])) {
            $query->andWhere('s.type_statut = :status')
                ->setParameter('status', $filters['status']);
        }
        
        if (!empty($filters['warehouse'])) {
            $query->andWhere('c.warehouse = :warehouse')
                ->setParameter('warehouse', $filters['warehouse']);
        }
        
        // Appliquer le tri
        foreach ($order as $field => $direction) {
            $query->addOrderBy("c.$field", $direction);
        }
        
        // Utiliser le Paginator de Doctrine pour une pagination efficace
        $paginator = new Paginator($query);
        
        return [
            'items' => iterator_to_array($paginator->getIterator()),
            'totalItems' => $paginator->count(),
            'totalPages' => ceil($paginator->count() / $limit),
            'currentPage' => $page
        ];
    }

    /**
     * Vérifie si un code tracking est unique
     */
    public function isCodeTrackingUnique(string $codeTracking): bool
    {
        return null === $this->findOneBy(['codeTracking' => $codeTracking]);
    }

    /**
     * Récupère les derniers colis ajoutés
     */
    public function findLatest(int $limit = 5): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.statuts', 's', 'WITH', 's.id = (
                SELECT MAX(s2.id) FROM App\Entity\Statut s2 WHERE s2.colis = c
            )')
            ->leftJoin('c.destinataire', 'd')
            ->leftJoin('c.expediteur', 'e')
            ->addSelect('s', 'd', 'e')
            ->orderBy('c.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte le nombre de colis créés dans les X derniers jours
     */
    public function countRecentColis(int $days): int
    {
        $date = new \DateTime();
        $date->modify('-' . $days . ' days');

        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.date_creation >= :date')
            ->setParameter('date', $date)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Compte le nombre de colis par mois pour l'année courante
     */
    public function countMonthlyForYear(int $year = null): array
    {
        $year = $year ?? (int) date('Y');
        
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
            SELECT EXTRACT(MONTH FROM c.date_creation) as month, COUNT(c.id) as count
            FROM colis c
            WHERE EXTRACT(YEAR FROM c.date_creation) = :year
            GROUP BY EXTRACT(MONTH FROM c.date_creation)
            ORDER BY month ASC
        ';
        
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery(['year' => $year]);
        $results = $resultSet->fetchAllAssociative();
        
        // Initialiser tous les mois avec 0
        $months = array_fill(1, 12, 0);
        
        // Remplir avec les données réelles
        foreach ($results as $result) {
            $months[(int)$result['month']] = (int)$result['count'];
        }
        
        return $months;
    }
    
    /**
     * Compte le nombre de colis livrés par mois pour l'année courante
     */
    public function countDeliveredMonthlyForYear(int $year = null): array
    {
        $year = $year ?? (int) date('Y');
        
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
            SELECT EXTRACT(MONTH FROM s.date_statut) as month, COUNT(DISTINCT c.id) as count
            FROM colis c
            JOIN statut s ON s.colis_id = c.id
            WHERE EXTRACT(YEAR FROM s.date_statut) = :year
            AND s.type_statut = :deliveredStatus
            GROUP BY EXTRACT(MONTH FROM s.date_statut)
            ORDER BY month ASC
        ';
        
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery([
            'year' => $year,
            'deliveredStatus' => StatusType::LIVRE->value
        ]);
        $results = $resultSet->fetchAllAssociative();
        
        // Initialiser tous les mois avec 0
        $months = array_fill(1, 12, 0);
        
        // Remplir avec les données réelles
        foreach ($results as $result) {
            $months[(int)$result['month']] = (int)$result['count'];
        }
        
        return $months;
    }
    
    /**
     * Calcule le taux de livraison global
     * (pourcentage de colis livrés par rapport au total)
     */
    public function calculateDeliveryRate(): float
    {
        $totalColis = $this->count([]);
        
        if ($totalColis === 0) {
            return 0;
        }
        
        $deliveredColis = $this->createQueryBuilder('c')
            ->select('COUNT(DISTINCT c.id)')
            ->join('c.statuts', 's')
            ->where('s.type_statut = :deliveredStatus')
            ->setParameter('deliveredStatus', StatusType::LIVRE->value)
            ->getQuery()
            ->getSingleScalarResult();
        
        return ($deliveredColis / $totalColis) * 100;
    }
    
    /**
     * Trouve les colis par entrepôt
     */
    public function findByWarehouse(Warehouse $warehouse): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.warehouse = :warehouse')
            ->setParameter('warehouse', $warehouse)
            ->orderBy('c.date_creation', 'DESC')
            ->getQuery()
            ->getResult();
    }
    
    /**
     * Trouve les colis avec des alertes potentielles 
     * (bloqués en douane, retournés, etc.)
     */
    public function findColisWithAlerts(): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.statuts', 's', 'WITH', 's.id = (
                SELECT MAX(s2.id) FROM App\Entity\Statut s2 WHERE s2.colis = c
            )')
            ->andWhere('s.type_statut IN (:alertStatuses)')
            ->setParameter('alertStatuses', [
                StatusType::BLOQUE_DOUANE->value,
                StatusType::RETOURNE->value
            ])
            ->orderBy('s.date_statut', 'ASC')
            ->getQuery()
            ->getResult();
    }
    
    /**
     * Calcule la distribution des colis par pays de destination
     */
    public function getDestinationCountryDistribution(): array
    {
        return $this->createQueryBuilder('c')
            ->select('d.pays as country, COUNT(c.id) as count')
            ->join('c.destinataire', 'd')
            ->groupBy('d.pays')
            ->orderBy('count', 'DESC')
            ->getQuery()
            ->getResult();
    }
    
    /**
     * Trouve les colis qui n'ont pas été mis à jour depuis X jours
     */
    public function findStagnantColis(int $days): array
    {
        $date = new \DateTime();
        $date->modify("-{$days} days");
        
        return $this->createQueryBuilder('c')
            ->join('c.statuts', 's', 'WITH', 's.id = (
                SELECT MAX(s2.id) FROM App\Entity\Statut s2 WHERE s2.colis = c
            )')
            ->andWhere('s.date_statut <= :threshold')
            ->andWhere('s.type_statut NOT IN (:finalStatuses)')
            ->setParameter('threshold', $date)
            ->setParameter('finalStatuses', [
                StatusType::LIVRE->value,
                StatusType::RETOURNE->value
            ])
            ->orderBy('s.date_statut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findForExport(array $criteria = []): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.expediteur', 'e')
            ->leftJoin('c.destinataire', 'd')
            ->leftJoin('c.statuts', 's')
            ->addSelect('e', 'd', 's')
            ->orderBy('c.id', 'DESC');
        
        // Apply criteria filters if provided
        if (!empty($criteria['dateMin'])) {
            $qb->andWhere('c.date_creation >= :dateMin')
               ->setParameter('dateMin', new \DateTime($criteria['dateMin']));
        }
        
        if (!empty($criteria['dateMax'])) {
            $qb->andWhere('c.date_creation <= :dateMax')
               ->setParameter('dateMax', new \DateTime($criteria['dateMax']));
        }
        
        if (!empty($criteria['status'])) {
            $qb->andWhere('s.type_statut = :status')
               ->setParameter('status', $criteria['status']);
        }
        
        return $qb->getQuery()->getResult();
    }

    /**
     * Get dashboard statistics for colis
     * 
     * @return array Statistics about colis (total, trends, etc.)
     */
    public function getDashboardStats(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        
        // Get total count
        $totalCount = $this->count([]);
        
        // Get count for current month
        $currentMonthStart = new \DateTime('first day of this month');
        $currentMonthFormatted = $currentMonthStart->format('Y-m-d');
        
        $currentMonthSQL = "SELECT COUNT(id) as count FROM colis 
                          WHERE date_creation >= :date";
        $currentMonthResult = $conn->executeQuery($currentMonthSQL, [
            'date' => $currentMonthFormatted
        ])->fetchAssociative();
        $currentMonthCount = (int)$currentMonthResult['count'];
        
        // Get count for previous month for trend calculation
        $prevMonthStart = (clone $currentMonthStart)->modify('-1 month');
        $prevMonthFormatted = $prevMonthStart->format('Y-m-d');
        
        $prevMonthSQL = "SELECT COUNT(id) as count FROM colis 
                       WHERE date_creation >= :prevDate AND date_creation < :currentDate";
        $prevMonthResult = $conn->executeQuery($prevMonthSQL, [
            'prevDate' => $prevMonthFormatted,
            'currentDate' => $currentMonthFormatted
        ])->fetchAssociative();
        $prevMonthCount = (int)$prevMonthResult['count'];
        
        // Calculate trend percentage
        $trend = 0;
        if ($prevMonthCount > 0) {
            $trend = round((($currentMonthCount - $prevMonthCount) / $prevMonthCount) * 100);
        }
        
        return [
            'total' => $totalCount,
            'current_month' => $currentMonthCount,
            'prev_month' => $prevMonthCount,
            'trend' => $trend
        ];
    }

    /**
     * Get monthly colis data for chart display
     * 
     * @return array Monthly data for sent and delivered packages
     */
    public function getMonthlyData(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        
        $currentYear = (new \DateTime())->format('Y');
        
        // Get data for packages created each month
        $sentSQL = "SELECT MONTH(date_creation) as month, COUNT(id) as count 
                  FROM colis 
                  WHERE YEAR(date_creation) = :year 
                  GROUP BY MONTH(date_creation)";
        
        $sentData = $conn->executeQuery($sentSQL, ['year' => $currentYear])->fetchAllAssociative();
        
        // Get data for packages delivered each month  
        $deliveredSQL = "SELECT MONTH(s.date_statut) as month, COUNT(DISTINCT c.id) as count 
                       FROM colis c
                       JOIN statut s ON s.colis_id = c.id
                       WHERE s.type_statut = 'livre' AND YEAR(s.date_statut) = :year
                       GROUP BY MONTH(s.date_statut)";
        
        $deliveredData = $conn->executeQuery($deliveredSQL, ['year' => $currentYear])->fetchAllAssociative();
        
        // Format the data for all 12 months
        $sent = array_fill(0, 12, 0);
        $delivered = array_fill(0, 12, 0);
        
        foreach ($sentData as $row) {
            $sent[$row['month'] - 1] = (int)$row['count'];
        }
        
        foreach ($deliveredData as $row) {
            $delivered[$row['month'] - 1] = (int)$row['count'];
        }
        
        return [
            'sent' => $sent,
            'delivered' => $delivered
        ];
    }

    /**
     * Get status distribution data for charts
     * 
     * @return array Count of colis by status
     */
    public function getStatusDistribution(): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select('s.type_statut as status, COUNT(c.id) as count')
            ->leftJoin('c.statuts', 's')
            ->where('s.id IN (
                SELECT MAX(s2.id) FROM App\Entity\Statut s2 WHERE s2.colis = c.id
            )')
            ->groupBy('s.type_statut');
        
        $results = $qb->getQuery()->getResult();
        
        // Format the results
        $statuses = ['en_attente', 'recu', 'en_transit', 'en_livraison', 'livre', 'retourne', 'bloque_douane'];
        $data = array_fill_keys($statuses, 0);
        
        foreach ($results as $row) {
            $status = $row['status'] ?? 'en_attente';
            $data[$status] = (int)$row['count'];
        }
        
        return array_values($data); // Return just the values in order
    }

    /**
     * Compte les colis créés ce mois-ci
     */
    public function countThisMonth(): int
    {
        $startOfMonth = new \DateTime('first day of this month 00:00:00');
        $endOfMonth = new \DateTime('last day of this month 23:59:59');
        
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.date_creation BETWEEN :start AND :end')
            ->setParameter('start', $startOfMonth)
            ->setParameter('end', $endOfMonth)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Compte les colis créés le mois dernier
     */
    public function countLastMonth(): int
    {
        $startOfLastMonth = new \DateTime('first day of last month 00:00:00');
        $endOfLastMonth = new \DateTime('last day of last month 23:59:59');
        
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.date_creation BETWEEN :start AND :end')
            ->setParameter('start', $startOfLastMonth)
            ->setParameter('end', $endOfLastMonth)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Récupère les colis créés par mois pour l'année en cours
     */
    public function getMonthlyCreatedColis(): array
    {
        $result = $this->createQueryBuilder('c')
            ->select('MONTH(c.date_creation) as month, COUNT(c.id) as count')
            ->where('YEAR(c.date_creation) = :year')
            ->setParameter('year', date('Y'))
            ->groupBy('month')
            ->orderBy('month', 'ASC')
            ->getQuery()
            ->getResult();
        
        $monthlyData = [];
        foreach ($result as $row) {
            $monthlyData[(int)$row['month']] = (int)$row['count'];
        }
        
        return $monthlyData;
    }

    /**
     * Récupère les colis livrés par mois
     */
    public function getMonthlyDeliveredColis(): array
    {
        $result = $this->createQueryBuilder('c')
            ->select('MONTH(s.date_statut) as month, COUNT(DISTINCT c.id) as count')
            ->join('c.statuts', 's')
            ->where('s.type_statut = :status')
            ->andWhere('YEAR(s.date_statut) = :year')
            ->setParameter('status', 'livre')
            ->setParameter('year', date('Y'))
            ->groupBy('month')
            ->orderBy('month', 'ASC')
            ->getQuery()
            ->getResult();
        
        $monthlyData = [];
        foreach ($result as $row) {
            $monthlyData[(int)$row['month']] = (int)$row['count'];
        }
        
        return $monthlyData;
    }

    /**
     * Récupère les colis récents avec détails
     */
    public function getRecentColisWithDetails(int $limit = 10): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.expediteur', 'e')
            ->leftJoin('c.destinataire', 'd')
            ->leftJoin('c.statuts', 's')
            ->addSelect('e', 'd', 's')
            ->orderBy('c.date_creation', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les colis prévus pour livraison aujourd'hui
     */
    public function getColisForDeliveryToday(): array
    {
        $today = new \DateTime('today');
        $tomorrow = new \DateTime('tomorrow');
        
        return $this->createQueryBuilder('c')
            ->join('c.statuts', 's')
            ->where('s.type_statut = :status')
            ->andWhere('s.date_statut BETWEEN :today AND :tomorrow')
            ->setParameter('status', 'en_livraison')
            ->setParameter('today', $today)
            ->setParameter('tomorrow', $tomorrow)
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les colis nécessitant une vérification
     */
    public function getColisNeedingVerification(): array
    {
        $threeDaysAgo = new \DateTime('-3 days');
        
        return $this->createQueryBuilder('c')
            ->join('c.statuts', 's')
            ->where('s.type_statut = :status')
            ->andWhere('s.date_statut < :date')
            ->setParameter('status', 'en_transit')
            ->setParameter('date', $threeDaysAgo)
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte les livraisons à temps
     */
    public function getOnTimeDeliveryCount(): int
    {
        // Définir "à temps" comme livré dans les 7 jours suivant la création
        // Using PostgreSQL-compatible date calculation
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
            SELECT COUNT(c.id) as count
            FROM colis c
            INNER JOIN statut s ON s.colis_id = c.id
            WHERE s.type_statut = :status
            AND EXTRACT(DAY FROM (s.date_statut - c.date_creation)) <= 7
        ';
        
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery(['status' => 'livre']);
        $row = $result->fetchAssociative();
        
        return $row ? (int)$row['count'] : 0;
    }

    /**
     * Compte le total des livraisons
     */
    public function getTotalDeliveryCount(): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->join('c.statuts', 's')
            ->where('s.type_statut = :status')
            ->setParameter('status', 'livre')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
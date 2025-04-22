<?php

namespace App\Repository;

use App\Entity\Statut;
use App\Enum\StatusType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;

/**
 * @extends ServiceEntityRepository<Statut>
 */
class StatutRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Statut::class);
    }

    /**
     * Compte le nombre de statuts par type
     */
    public function countByType(StatusType $statusType): int
    {
        return $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->where('s.type_statut = :type') // Using snake_case field name
            ->setParameter('type', $statusType->value)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Récupère la distribution des statuts actuels
     * @return array Tableau associatif [type_statut => count]
     */
    public function getStatusDistribution(): array
    {
        $qb = $this->createQueryBuilder('s');
        $qb->select('s.type_statut, COUNT(s.id) as count') // Using snake_case field name
           ->groupBy('s.type_statut'); // Using snake_case field name
        
        $results = $qb->getQuery()->getResult();
        
        $distribution = [];
        foreach ($results as $result) {
            $distribution[$result['type_statut']] = $result['count'];
        }
        
        return $distribution;
    }
    
    /**
     * Récupère les derniers statuts pour chaque colis
     * @return Statut[] Liste des derniers statuts pour chaque colis
     */
    public function findLatestStatusForEachColis(): array
    {
        // Requête pour trouver l'ID du dernier statut pour chaque colis
        $subQuery = $this->getEntityManager()->createQueryBuilder()
            ->select('MAX(s2.id)')
            ->from('App\Entity\Statut', 's2')
            ->where('s2.colis = s.colis')
            ->getDQL();
        
        // Requête principale
        return $this->createQueryBuilder('s')
            ->where("s.id IN ({$subQuery})")
            ->orderBy('s.date_statut', 'DESC') // Using snake_case field name
            ->getQuery()
            ->getResult();
    }
    
    /**
     * Trouve les colis bloqués en douane ou avec problème depuis X jours
     */
    public function findBlockedColisForDays(int $days): array
    {
        $date = new \DateTime();
        $date->modify("-{$days} days");
        
        return $this->createQueryBuilder('s')
            ->select('s', 'c')
            ->join('s.colis', 'c')
            ->where('s.type_statut IN (:blockedTypes)') // Using snake_case field name
            ->andWhere('s.date_statut <= :threshold') // Using snake_case field name
            ->andWhere('s.id IN (
                SELECT MAX(s2.id) FROM App\Entity\Statut s2 WHERE s2.colis = s.colis
            )')
            ->setParameter('blockedTypes', [
                StatusType::BLOQUE_DOUANE->value, 
                StatusType::RETOURNE->value
            ])
            ->setParameter('threshold', $date)
            ->orderBy('s.date_statut', 'ASC') // Using snake_case field name
            ->getQuery()
            ->getResult();
    }
    
    /**
     * Compte les transitions de statut par mois
     * @return array Tableau associatif [mois => [type => count]]
     */
    public function countStatusTransitionsByMonth(int $year = null): array
    {
        $year = $year ?? (int) date('Y');
        
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
            SELECT 
                EXTRACT(MONTH FROM s.date_statut) as month,
                s.type_statut as type,
                COUNT(s.id) as count
            FROM statut s
            WHERE EXTRACT(YEAR FROM s.date_statut) = :year
            GROUP BY EXTRACT(MONTH FROM s.date_statut), s.type_statut
            ORDER BY month ASC, count DESC
        ';
        
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery(['year' => $year]);
        $results = $resultSet->fetchAllAssociative();
        
        // Organiser les résultats par mois
        $monthlyData = [];
        foreach ($results as $result) {
            $month = (int)$result['month'];
            $type = $result['type'];
            $count = (int)$result['count'];
            
            if (!isset($monthlyData[$month])) {
                $monthlyData[$month] = [];
            }
            
            $monthlyData[$month][$type] = $count;
        }
        
        return $monthlyData;
    }
    
    /**
     * Calcule le temps moyen passé dans chaque statut
     */
    public function calculateAverageTimeInStatus(): array
    {
        // Cette requête est complexe et dépendrait de la façon dont vous voulez mesurer le temps dans chaque statut
        // Voici une version simplifiée pour l'exemple
        
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
            WITH StatusTransitions AS (
                SELECT 
                    s1.colis_id,
                    s1.type_statut,
                    s1.date_statut as start_date,
                    MIN(s2.date_statut) as end_date
                FROM statut s1
                LEFT JOIN statut s2 ON 
                    s1.colis_id = s2.colis_id AND 
                    s1.date_statut < s2.date_statut
                GROUP BY s1.id, s1.colis_id, s1.type_statut, s1.date_statut
            )
            SELECT 
                type_statut,
                AVG(EXTRACT(EPOCH FROM (COALESCE(end_date, CURRENT_TIMESTAMP) - start_date))/3600) as avg_hours
            FROM StatusTransitions
            GROUP BY type_statut
            ORDER BY avg_hours DESC
        ';
        
        try {
            $stmt = $conn->prepare($sql);
            $resultSet = $stmt->executeQuery();
            $results = $resultSet->fetchAllAssociative();
            
            $averageTimes = [];
            foreach ($results as $result) {
                $averageTimes[$result['type_statut']] = round($result['avg_hours'], 1); // En heures, arrondi à 1 décimale
            }
            
            return $averageTimes;
        } catch (\Exception $e) {
            // Log l'erreur et retourne un tableau vide
            return [];
        }
    }
}
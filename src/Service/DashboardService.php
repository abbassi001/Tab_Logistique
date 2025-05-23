<?php

namespace App\Service;

use App\Repository\ColisRepository;
use App\Repository\StatutRepository;
use App\Repository\UserRepository;
use App\Enum\StatusType;

class DashboardService
{
    private ColisRepository $colisRepository;
    private StatutRepository $statutRepository;
    private UserRepository $userRepository;

    public function __construct(
        ColisRepository $colisRepository,
        StatutRepository $statutRepository,
        UserRepository $userRepository
    ) {
        $this->colisRepository = $colisRepository;
        $this->statutRepository = $statutRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Calcule les métriques de performance
     */
    public function calculatePerformanceMetrics(): array
    {
        $totalColis = $this->colisRepository->count([]);
        
        if ($totalColis === 0) {
            return [
                'delivery_rate' => 0,
                'average_delivery_time' => 0,
                'customer_satisfaction' => 0,
                'processing_efficiency' => 0,
            ];
        }

        try {
            $deliveredColis = $this->statutRepository->countColisByStatus(StatusType::LIVRE);
            $avgDeliveryTime = $this->statutRepository->getAverageDeliveryTime();

            return [
                'delivery_rate' => round(($deliveredColis / $totalColis) * 100, 1),
                'average_delivery_time' => $avgDeliveryTime,
                'customer_satisfaction' => $this->calculateCustomerSatisfaction(),
                'processing_efficiency' => $this->calculateProcessingEfficiency(),
            ];
        } catch (\Exception $e) {
            // En cas d'erreur, retourner des valeurs par défaut
            return [
                'delivery_rate' => 0,
                'average_delivery_time' => 0,
                'customer_satisfaction' => 0,
                'processing_efficiency' => 0,
            ];
        }
    }

    /**
     * Génère des alertes intelligentes
     */
    public function generateSmartAlerts(): array
    {
        $alerts = [];

        try {
            // Alertes basées sur les délais
            $longTransitColis = $this->statutRepository->getColisInLongTransit(10);
            foreach ($longTransitColis as $statut) {
                $alerts[] = [
                    'type' => 'warning',
                    'priority' => 'medium',
                    'message' => "Colis {$statut->getColis()->getCodeTracking()} en transit depuis " . 
                               $statut->getDateStatut()->diff(new \DateTime())->days . " jours",
                    'date' => $statut->getDateStatut(),
                ];
            }

            // Alertes basées sur les volumes
            $pendingCount = $this->statutRepository->countColisByStatus(StatusType::EN_ATTENTE);
            if ($pendingCount > 20) {
                $alerts[] = [
                    'type' => $pendingCount > 50 ? 'danger' : 'warning',
                    'priority' => $pendingCount > 50 ? 'high' : 'medium',
                    'message' => "{$pendingCount} colis en attente de traitement",
                    'date' => new \DateTime(),
                ];
            }

            // Alertes basées sur les performances
            $metrics = $this->calculatePerformanceMetrics();
            if ($metrics['delivery_rate'] < 85 && $metrics['delivery_rate'] > 0) {
                $alerts[] = [
                    'type' => 'warning',
                    'priority' => 'medium',
                    'message' => "Taux de livraison faible: {$metrics['delivery_rate']}%",
                    'date' => new \DateTime(),
                ];
            }

            // Colis bloqués en douane
            $blockedColis = $this->statutRepository->getColisBlockedInCustoms(3);
            foreach ($blockedColis as $statut) {
                $daysSince = (new \DateTime())->diff($statut->getDateStatut())->days;
                $alerts[] = [
                    'type' => 'danger',
                    'priority' => 'high',
                    'message' => sprintf('Colis %s bloqué en douane depuis %d jours', 
                        $statut->getColis()->getCodeTracking(), $daysSince),
                    'date' => $statut->getDateStatut(),
                ];
            }

        } catch (\Exception $e) {
            // En cas d'erreur, ajouter une alerte système
            $alerts[] = [
                'type' => 'danger',
                'priority' => 'high',
                'message' => 'Erreur lors de la génération des alertes système',
                'date' => new \DateTime(),
            ];
        }

        // Trier par priorité et date
        usort($alerts, function($a, $b) {
            $priorityOrder = ['high' => 3, 'medium' => 2, 'low' => 1];
            $aPriority = $priorityOrder[$a['priority']] ?? 0;
            $bPriority = $priorityOrder[$b['priority']] ?? 0;
            
            if ($aPriority === $bPriority) {
                return $b['date'] <=> $a['date'];
            }
            
            return $bPriority <=> $aPriority;
        });

        return array_slice($alerts, 0, 10);
    }

    /**
     * Génère des tâches intelligentes
     */
    public function generateSmartTasks(): array
    {
        $tasks = [];

        try {
            // Tâches basées sur les données réelles
            $pendingCount = $this->statutRepository->countColisByStatus(StatusType::EN_ATTENTE);
            if ($pendingCount > 0) {
                $tasks[] = [
                    'description' => "Traiter {$pendingCount} colis en attente",
                    'priority' => $pendingCount > 20 ? 'high' : 'medium',
                    'deadline' => new \DateTime('+1 day'),
                    'count' => $pendingCount,
                ];
            }

            // Colis à livrer aujourd'hui
            $deliveryToday = $this->colisRepository->getColisForDeliveryToday();
            if (!empty($deliveryToday)) {
                $tasks[] = [
                    'description' => "Coordonner " . count($deliveryToday) . " livraisons prévues aujourd'hui",
                    'priority' => 'high',
                    'deadline' => new \DateTime('today 18:00'),
                    'count' => count($deliveryToday),
                ];
            }

            // Tâches de reporting
            if (date('j') === '1') { // Premier du mois
                $tasks[] = [
                    'description' => 'Préparer le rapport mensuel',
                    'priority' => 'medium',
                    'deadline' => new \DateTime('+2 days'),
                    'count' => 1,
                ];
            }

            // Tâches de maintenance
            if (date('N') === '5') { // Vendredi
                $tasks[] = [
                    'description' => 'Vérification hebdomadaire du système',
                    'priority' => 'low',
                    'deadline' => new \DateTime('today 17:00'),
                    'count' => 1,
                ];
            }

        } catch (\Exception $e) {
            // En cas d'erreur, ajouter une tâche de diagnostic
            $tasks[] = [
                'description' => 'Vérifier les erreurs système',
                'priority' => 'high',
                'deadline' => new \DateTime('+1 hour'),
                'count' => 1,
            ];
        }

        return $tasks;
    }

    private function calculateCustomerSatisfaction(): float
    {
        try {
            $onTimeDeliveries = $this->colisRepository->getOnTimeDeliveryCount();
            $totalDeliveries = $this->colisRepository->getTotalDeliveryCount();
            $returnedColis = $this->statutRepository->countColisByStatus(StatusType::RETOURNE);
            
            if ($totalDeliveries === 0) {
                return 0;
            }
            
            $onTimeRate = ($onTimeDeliveries / $totalDeliveries) * 100;
            $returnRate = ($returnedColis / $totalDeliveries) * 100;
            
            return max(0, min(100, $onTimeRate - ($returnRate * 2)));
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function calculateProcessingEfficiency(): float
    {
        try {
            $avgProcessingTime = $this->statutRepository->getAverageProcessingTime();
            $targetProcessingTime = 24; // 24 heures cible
            
            if ($avgProcessingTime === 0) {
                return 100;
            }
            
            return min(100, max(0, ($targetProcessingTime / $avgProcessingTime) * 100));
        } catch (\Exception $e) {
            return 0;
        }
    }
}
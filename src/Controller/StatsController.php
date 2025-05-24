<?php

namespace App\Controller;

use App\Repository\ColisRepository;
use App\Repository\UserRepository;
use App\Repository\ExpediteurRepository;
use App\Repository\DestinataireRepository;
use App\Repository\TransportRepository;
use App\Repository\WarehouseRepository;
use App\Repository\PhotoRepository;
use App\Repository\DocumentSupportRepository;
use App\Repository\StatutRepository;
use App\Enum\StatusType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Contrôleur pour les statistiques utilisées dans la sidebar et les widgets
 * Ces méthodes sont appelées via render() dans les templates Twig
 */
#[Route('/stats')]
class StatsController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ColisRepository $colisRepository,
        private UserRepository $userRepository,
        private ExpediteurRepository $expediteurRepository,
        private DestinataireRepository $destinataireRepository,
        private TransportRepository $transportRepository,
        private WarehouseRepository $warehouseRepository,
        private PhotoRepository $photoRepository,
        private DocumentSupportRepository $documentSupportRepository,
        private StatutRepository $statutRepository
    ) {}

    /**
     * Nombre total de colis
     */
    public function getTotalColis(): Response
    {
        try {
            $count = $this->colisRepository->count([]);
            return new Response($count);
        } catch (\Exception $e) {
            return new Response('0');
        }
    }

    /**
     * Nombre de colis livrés aujourd'hui
     */
    public function getDeliveredToday(): Response
    {
        try {
            $today = new \DateTime('today 00:00:00');
            $tomorrow = new \DateTime('tomorrow 00:00:00');

            $count = $this->entityManager->createQuery(
                'SELECT COUNT(DISTINCT c.id) 
                 FROM App\Entity\Colis c 
                 JOIN c.statuts s 
                 WHERE s.type_statut = :deliveredStatus 
                 AND s.date_statut >= :today 
                 AND s.date_statut < :tomorrow'
            )
            ->setParameter('deliveredStatus', StatusType::LIVRE->value)
            ->setParameter('today', $today)
            ->setParameter('tomorrow', $tomorrow)
            ->getSingleScalarResult();

            return new Response($count);
        } catch (\Exception $e) {
            return new Response('0');
        }
    }

    /**
     * Nombre de colis en attente
     */
    public function getPendingColis(): Response
    {
        try {
            $count = $this->entityManager->createQuery(
                'SELECT COUNT(DISTINCT c.id) 
                 FROM App\Entity\Colis c 
                 JOIN c.statuts s 
                 WHERE s.type_statut = :pendingStatus'
            )
            ->setParameter('pendingStatus', StatusType::EN_ATTENTE->value)
            ->getSingleScalarResult();

            return new Response($count);
        } catch (\Exception $e) {
            return new Response('0');
        }
    }

    /**
     * Nombre de colis avec problèmes (bloqués en douane + retournés)
     */
    public function getBlockedColis(): Response
    {
        try {
            $count = $this->entityManager->createQuery(
                'SELECT COUNT(DISTINCT c.id) 
                 FROM App\Entity\Colis c 
                 JOIN c.statuts s 
                 WHERE s.type_statut IN (:problemStatuses)'
            )
            ->setParameter('problemStatuses', [
                StatusType::BLOQUE_DOUANE->value,
                StatusType::RETOURNE->value
            ])
            ->getSingleScalarResult();

            return new Response($count);
        } catch (\Exception $e) {
            return new Response('0');
        }
    }

    /**
     * Nombre total d'utilisateurs
     */
    #[IsGranted('ROLE_ADMIN')]
    public function getTotalUsers(): Response
    {
        try {
            $count = $this->userRepository->count([]);
            return new Response($count);
        } catch (\Exception $e) {
            return new Response('0');
        }
    }

    /**
     * Nombre d'utilisateurs actifs
     */
    #[IsGranted('ROLE_ADMIN')]
    public function getActiveUsers(): Response
    {
        try {
            $count = $this->userRepository->count(['isActive' => true]);
            return new Response($count);
        } catch (\Exception $e) {
            return new Response('0');
        }
    }

    /**
     * Nombre total de photos
     */
    public function getTotalPhotos(): Response
    {
        try {
            $count = $this->photoRepository->count([]);
            return new Response($count);
        } catch (\Exception $e) {
            return new Response('0');
        }
    }

    /**
     * Nombre total de documents
     */
    public function getTotalDocuments(): Response
    {
        try {
            $count = $this->documentSupportRepository->count([]);
            return new Response($count);
        } catch (\Exception $e) {
            return new Response('0');
        }
    }

    /**
     * Nombre total d'expéditeurs
     */
    public function getTotalExpediteurs(): Response
    {
        try {
            $count = $this->expediteurRepository->count([]);
            return new Response($count);
        } catch (\Exception $e) {
            return new Response('0');
        }
    }

    /**
     * Nombre total de destinataires
     */
    public function getTotalDestinataires(): Response
    {
        try {
            $count = $this->destinataireRepository->count([]);
            return new Response($count);
        } catch (\Exception $e) {
            return new Response('0');
        }
    }

    /**
     * Nombre total de transports
     */
    public function getTotalTransports(): Response
    {
        try {
            $count = $this->transportRepository->count([]);
            return new Response($count);
        } catch (\Exception $e) {
            return new Response('0');
        }
    }

    /**
     * Nombre total d'entrepôts
     */
    public function getTotalWarehouses(): Response
    {
        try {
            $count = $this->warehouseRepository->count([]);
            return new Response($count);
        } catch (\Exception $e) {
            return new Response('0');
        }
    }

    /**
     * Statistiques complètes pour les widgets (retourne du JSON)
     */
    #[Route('/widget-data', name: 'app_stats_widget_data', methods: ['GET'])]
    public function getWidgetData(): JsonResponse
    {
        try {
            $data = [
                'colis' => [
                    'total' => $this->colisRepository->count([]),
                    'delivered_today' => $this->getDeliveredTodayCount(),
                    'pending' => $this->getPendingColisCount(),
                    'blocked' => $this->getBlockedColisCount(),
                ],
                'entities' => [
                    'expediteurs' => $this->expediteurRepository->count([]),
                    'destinataires' => $this->destinataireRepository->count([]),
                    'transports' => $this->transportRepository->count([]),
                    'warehouses' => $this->warehouseRepository->count([]),
                ],
                'documents' => [
                    'photos' => $this->photoRepository->count([]),
                    'documents' => $this->documentSupportRepository->count([]),
                ]
            ];

            // Ajouter les stats admin si autorisé
            if ($this->isGranted('ROLE_ADMIN')) {
                $data['admin'] = [
                    'total_users' => $this->userRepository->count([]),
                    'active_users' => $this->userRepository->count(['isActive' => true]),
                    'connected_today' => $this->getConnectedTodayCount(),
                ];
            }

            return new JsonResponse($data);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors du chargement des statistiques'], 500);
        }
    }

    /**
     * Statistiques par statut pour les graphiques
     */
    #[Route('/status-chart', name: 'app_stats_status_chart', methods: ['GET'])]
    public function getStatusChartData(): JsonResponse
    {
        try {
            $statusCounts = [];
            
            foreach (StatusType::cases() as $statusType) {
                $count = $this->statutRepository->countByType($statusType);
                $statusCounts[$statusType->getLabel()] = $count;
            }

            return new JsonResponse([
                'labels' => array_keys($statusCounts),
                'data' => array_values($statusCounts),
                'colors' => [
                    '#ffc107', // En attente - warning
                    '#20c997', // Reçu - teal
                    '#0dcaf0', // En transit - info
                    '#0d6efd', // En livraison - primary
                    '#198754', // Livré - success
                    '#dc3545', // Retourné - danger
                    '#fd7e14', // Bloqué douane - orange
                ]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors du chargement des données de statut'], 500);
        }
    }

    /**
     * Évolution mensuelle des colis
     */
    #[Route('/monthly-evolution', name: 'app_stats_monthly_evolution', methods: ['GET'])]
    public function getMonthlyEvolution(): JsonResponse
    {
        try {
            $monthlyData = $this->colisRepository->countMonthlyForYear();
            $frenchMonths = [
                1 => 'Jan', 2 => 'Fév', 3 => 'Mar', 4 => 'Avr', 5 => 'Mai', 6 => 'Juin',
                7 => 'Juil', 8 => 'Août', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Déc'
            ];

            $labels = [];
            $data = [];

            for ($i = 1; $i <= 12; $i++) {
                $labels[] = $frenchMonths[$i];
                $data[] = $monthlyData[$i] ?? 0;
            }

            return new JsonResponse([
                'labels' => $labels,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors du chargement de l\'évolution mensuelle'], 500);
        }
    }

    /**
     * Performance des entrepôts
     */
    #[Route('/warehouse-performance', name: 'app_stats_warehouse_performance', methods: ['GET'])]
    public function getWarehousePerformance(): JsonResponse
    {
        try {
            $warehouses = $this->warehouseRepository->findBy([], ['id' => 'DESC'], 5);
            $performance = [];

            foreach ($warehouses as $warehouse) {
                $totalColis = $this->colisRepository->count(['warehouse' => $warehouse]);
                
                $deliveredColis = $this->entityManager->createQuery(
                    'SELECT COUNT(DISTINCT c.id) 
                     FROM App\Entity\Colis c 
                     JOIN c.statuts s 
                     WHERE c.warehouse = :warehouse 
                     AND s.type_statut = :deliveredStatus'
                )
                ->setParameter('warehouse', $warehouse)
                ->setParameter('deliveredStatus', StatusType::LIVRE->value)
                ->getSingleScalarResult();

                $performanceRate = $totalColis > 0 ? round(($deliveredColis / $totalColis) * 100) : 0;

                $performance[] = [
                    'name' => $warehouse->getNomEntreprise() ?: $warehouse->getCodeUt(),
                    'code' => $warehouse->getCodeUt(),
                    'total' => $totalColis,
                    'delivered' => $deliveredColis,
                    'performance' => $performanceRate
                ];
            }

            // Trier par performance
            usort($performance, function($a, $b) {
                return $b['performance'] <=> $a['performance'];
            });

            return new JsonResponse($performance);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors du chargement des performances'], 500);
        }
    }

    /**
     * Alertes système
     */
    #[Route('/system-alerts', name: 'app_stats_system_alerts', methods: ['GET'])]
    public function getSystemAlerts(): JsonResponse
    {
        try {
            $alerts = [];

            // 1. Colis bloqués en douane depuis plus de 3 jours
            $blockedColis = $this->entityManager->createQuery(
                'SELECT COUNT(c.id) 
                FROM App\Entity\Colis c
                JOIN c.statuts s
                WHERE s.type_statut = :blockType
                AND s.date_statut < :threshold'
            )
            ->setParameter('blockType', StatusType::BLOQUE_DOUANE->value)
            ->setParameter('threshold', new \DateTime('-3 days'))
            ->getSingleScalarResult();

            if ($blockedColis > 0) {
                $alerts[] = [
                    'type' => 'warning',
                    'title' => 'Colis bloqués en douane',
                    'message' => "$blockedColis colis bloqués depuis plus de 3 jours",
                    'priority' => 'high',
                    'action_url' => null
                ];
            }

            // 2. Entrepôts à faible performance (< 70%)
            $lowPerformanceWarehouses = $this->getLowPerformanceWarehouses();
            if (count($lowPerformanceWarehouses) > 0) {
                $alerts[] = [
                    'type' => 'danger',
                    'title' => 'Performance entrepôts',
                    'message' => count($lowPerformanceWarehouses) . " entrepôt(s) avec performance < 70%",
                    'priority' => 'medium',
                    'action_url' => '/warehouse'
                ];
            }

            // 3. Utilisateurs inactifs (admin seulement)
            if ($this->isGranted('ROLE_ADMIN')) {
                $inactiveUsers = $this->userRepository->count(['isActive' => false]);
                if ($inactiveUsers > 0) {
                    $alerts[] = [
                        'type' => 'info',
                        'title' => 'Utilisateurs inactifs',
                        'message' => "$inactiveUsers compte(s) utilisateur désactivé(s)",
                        'priority' => 'low',
                        'action_url' => '/admin/user'
                    ];
                }
            }

            return new JsonResponse($alerts);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors du chargement des alertes'], 500);
        }
    }

    // Méthodes privées pour les calculs internes

    private function getDeliveredTodayCount(): int
    {
        $today = new \DateTime('today 00:00:00');
        $tomorrow = new \DateTime('tomorrow 00:00:00');

        return $this->entityManager->createQuery(
            'SELECT COUNT(DISTINCT c.id) 
             FROM App\Entity\Colis c 
             JOIN c.statuts s 
             WHERE s.type_statut = :deliveredStatus 
             AND s.date_statut >= :today 
             AND s.date_statut < :tomorrow'
        )
        ->setParameter('deliveredStatus', StatusType::LIVRE->value)
        ->setParameter('today', $today)
        ->setParameter('tomorrow', $tomorrow)
        ->getSingleScalarResult();
    }

    private function getPendingColisCount(): int
    {
        return $this->entityManager->createQuery(
            'SELECT COUNT(DISTINCT c.id) 
             FROM App\Entity\Colis c 
             JOIN c.statuts s 
             WHERE s.type_statut = :pendingStatus'
        )
        ->setParameter('pendingStatus', StatusType::EN_ATTENTE->value)
        ->getSingleScalarResult();
    }

    private function getBlockedColisCount(): int
    {
        return $this->entityManager->createQuery(
            'SELECT COUNT(DISTINCT c.id) 
             FROM App\Entity\Colis c 
             JOIN c.statuts s 
             WHERE s.type_statut IN (:problemStatuses)'
        )
        ->setParameter('problemStatuses', [
            StatusType::BLOQUE_DOUANE->value,
            StatusType::RETOURNE->value
        ])
        ->getSingleScalarResult();
    }

    private function getConnectedTodayCount(): int
    {
        $today = new \DateTime('today 00:00:00');
        
        return $this->entityManager->createQuery(
            'SELECT COUNT(u.id) FROM App\Entity\User u WHERE u.dernierConnexion >= :today'
        )
        ->setParameter('today', $today)
        ->getSingleScalarResult();
    }

    private function getLowPerformanceWarehouses(): array
    {
        $warehouses = $this->warehouseRepository->findAll();
        $lowPerformance = [];

        foreach ($warehouses as $warehouse) {
            $totalColis = $this->colisRepository->count(['warehouse' => $warehouse]);
            
            if ($totalColis > 0) {
                $deliveredColis = $this->entityManager->createQuery(
                    'SELECT COUNT(DISTINCT c.id) 
                     FROM App\Entity\Colis c 
                     JOIN c.statuts s 
                     WHERE c.warehouse = :warehouse 
                     AND s.type_statut = :deliveredStatus'
                )
                ->setParameter('warehouse', $warehouse)
                ->setParameter('deliveredStatus', StatusType::LIVRE->value)
                ->getSingleScalarResult();

                $performance = round(($deliveredColis / $totalColis) * 100);
                
                if ($performance < 70) {
                    $lowPerformance[] = [
                        'warehouse' => $warehouse,
                        'performance' => $performance
                    ];
                }
            }
        }

        return $lowPerformance;
    }
}
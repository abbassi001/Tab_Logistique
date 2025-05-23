<?php

namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class DashboardCacheService
{
    private CacheInterface $cache;
    private DashboardService $dashboardService;

    public function __construct(CacheInterface $cache, DashboardService $dashboardService)
    {
        $this->cache = $cache;
        $this->dashboardService = $dashboardService;
    }

    /**
     * Récupère les données du dashboard avec mise en cache
     */
    public function getCachedDashboardData(): array
    {
        return $this->cache->get('dashboard_data', function (ItemInterface $item) {
            $item->expiresAfter(300); // Cache pendant 5 minutes
            
            return [
                'performance_metrics' => $this->dashboardService->calculatePerformanceMetrics(),
                'alerts' => $this->dashboardService->generateSmartAlerts(),
                'tasks' => $this->dashboardService->generateSmartTasks(),
            ];
        });
    }

    /**
     * Invalide le cache du dashboard
     */
    public function invalidateCache(): void
    {
        $this->cache->delete('dashboard_data');
    }

    /**
     * Récupère les statistiques rapides (cache plus court)
     */
    public function getQuickStats(): array
    {
        return $this->cache->get('dashboard_quick_stats', function (ItemInterface $item) {
            $item->expiresAfter(60); // Cache pendant 1 minute
            
            return $this->dashboardService->calculatePerformanceMetrics();
        });
    }
}
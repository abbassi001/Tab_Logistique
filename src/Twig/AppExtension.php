<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('ago', [$this, 'timeAgo']),
            new TwigFilter('number_format', [$this, 'numberFormat']),
            new TwigFilter('percentage', [$this, 'percentage']),
            new TwigFilter('status_color', [$this, 'getStatusColor']),
            new TwigFilter('priority_color', [$this, 'getPriorityColor']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_status_icon', [$this, 'getStatusIcon']),
            new TwigFunction('get_priority_badge', [$this, 'getPriorityBadge']),
            new TwigFunction('format_file_size', [$this, 'formatFileSize']),
        ];
    }

    /**
     * Formate un timestamp en "il y a X temps"
     */
    public function timeAgo(\DateTimeInterface $date): string
    {
        $now = new \DateTime();
        $diff = $now->diff($date);

        if ($diff->y > 0) {
            return $diff->y . ' an' . ($diff->y > 1 ? 's' : '');
        } elseif ($diff->m > 0) {
            return $diff->m . ' mois';
        } elseif ($diff->d > 0) {
            return $diff->d . ' jour' . ($diff->d > 1 ? 's' : '');
        } elseif ($diff->h > 0) {
            return $diff->h . ' heure' . ($diff->h > 1 ? 's' : '');
        } elseif ($diff->i > 0) {
            return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '');
        } else {
            return 'À l\'instant';
        }
    }

    /**
     * Formate un nombre avec des séparateurs
     */
    public function numberFormat($number, int $decimals = 0, string $decPoint = ',', string $thousandsSep = ' '): string
    {
        if ($number === null) {
            return '0';
        }
        
        return number_format((float)$number, $decimals, $decPoint, $thousandsSep);
    }

    /**
     * Formate un pourcentage
     */
    public function percentage($value, int $decimals = 1): string
    {
        if ($value === null) {
            return '0%';
        }
        
        return number_format((float)$value, $decimals, ',', ' ') . '%';
    }

    /**
     * Retourne la couleur Bootstrap pour un statut
     */
    public function getStatusColor(string $status): string
    {
        $colors = [
            'EN_ATTENTE' => 'warning',
            'RECU' => 'info',
            'EN_TRANSIT' => 'primary',
            'EN_LIVRAISON' => 'success',
            'LIVRE' => 'success',
            'RETOURNE' => 'danger',
            'BLOQUE_DOUANE' => 'danger',
        ];

        return $colors[$status] ?? 'secondary';
    }

    /**
     * Retourne la couleur pour une priorité
     */
    public function getPriorityColor(string $priority): string
    {
        $colors = [
            'high' => 'danger',
            'medium' => 'warning',
            'low' => 'success',
        ];

        return $colors[$priority] ?? 'secondary';
    }

    /**
     * Retourne l'icône pour un statut
     */
    public function getStatusIcon(string $status): string
    {
        $icons = [
            'EN_ATTENTE' => 'fa-clock',
            'RECU' => 'fa-check',
            'EN_TRANSIT' => 'fa-truck',
            'EN_LIVRAISON' => 'fa-shipping-fast',
            'LIVRE' => 'fa-check-circle',
            'RETOURNE' => 'fa-undo',
            'BLOQUE_DOUANE' => 'fa-exclamation-triangle',
        ];

        return $icons[$status] ?? 'fa-question';
    }

    /**
     * Retourne un badge de priorité
     */
    public function getPriorityBadge(string $priority): string
    {
        $badges = [
            'high' => '<span class="badge bg-danger">Urgent</span>',
            'medium' => '<span class="badge bg-warning">Important</span>',
            'low' => '<span class="badge bg-success">Normal</span>',
        ];

        return $badges[$priority] ?? '<span class="badge bg-secondary">Non défini</span>';
    }

    /**
     * Formate une taille de fichier
     */
    public function formatFileSize(int $size): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $unit = 0;
        
        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }
        
        return round($size, 2) . ' ' . $units[$unit];
    }
}
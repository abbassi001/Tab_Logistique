<?php

namespace App\Enum;

enum StatusType: string
{
    case EN_ATTENTE = 'en_attente';
    case RECU = 'recu';
    case EN_TRANSIT = 'en_transit';
    case EN_LIVRAISON = 'en_livraison';
    case LIVRE = 'livre';
    case RETOURNE = 'retourne';
    case BLOQUE_DOUANE = 'bloque_douane';
    
    public function getLabel(): string
    {
        return match($this) {
            self::EN_ATTENTE => 'En attente',
            self::RECU => 'Reçu',
            self::EN_TRANSIT => 'En transit',
            self::EN_LIVRAISON => 'En livraison',
            self::LIVRE => 'Livré',
            self::RETOURNE => 'Retourné',
            self::BLOQUE_DOUANE => 'Bloqué en douane',
        };
    }
}
<?php
namespace App\Enum;

enum TypeEmballage: string
{
    case CARTON = 'carton';
    case PALETTE = 'palette';
    case VALISE = 'valise';
    case SAC = 'sac';
    case BOITE_METAL = 'boîte_métal';
    case PLASTIQUE = 'plastique';
    case AUTRE = 'autre';
}

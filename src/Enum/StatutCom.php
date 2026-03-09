<?php
namespace App\Enum;

enum StatutCom: String 
{
    case EN_ATTENTE = 'En attente';
    case EN_ATTENTE_PAIEMENT = 'En attente de paiement';
    case PAYEE = 'Payée';
    case LIVREE = 'Livrée';
    case ANNULEE = 'Annulée';


}


<?php

namespace App\Enums;

/** Permissions nommées du module OCD LIVE MARKET, vérifiées via Gate::authorize('market', ...). */
enum MarketPermission: string
{
    case View = 'MARKET_VIEW';
    case Review = 'MARKET_REVIEW';
    case Approve = 'MARKET_APPROVE';
    case Reject = 'MARKET_REJECT';
    case Remove = 'MARKET_REMOVE';
    case Manage = 'MARKET_MANAGE';
    case Stats = 'MARKET_STATS';
}

<?php

namespace App\Support;

class Pricing
{
    public const DISCOUNTS = [
        'bronze' => 0,
        'silver' => 5,
        'gold' => 10,
    ];

    public static function discountPercent(string $tier): int
    {
        return self::DISCOUNTS[$tier] ?? 0;
    }

    public static function priceForTier(int $baseCents, string $tier): int
    {
        return (int) round($baseCents * (1 - self::discountPercent($tier) / 100));
    }
}

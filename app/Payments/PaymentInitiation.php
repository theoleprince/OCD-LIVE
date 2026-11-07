<?php

namespace App\Payments;

final class PaymentInitiation
{
    public function __construct(
        public readonly ?string $redirectUrl = null,
        public readonly array $instructions = [],
    ) {}
}

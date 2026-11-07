<?php

namespace App\Payments;

use InvalidArgumentException;

class PaymentManager
{
    public function driver(?string $name = null): PaymentGateway
    {
        $name ??= config('payments.default');
        $class = config("payments.drivers.$name.class");

        if (! $class || ! class_exists($class)) {
            throw new InvalidArgumentException("Le pilote de paiement [$name] n'est pas encore configuré.");
        }

        return app($class);
    }
}

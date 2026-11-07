<?php

namespace App\Exceptions;

use RuntimeException;

class SlotUnavailableException extends RuntimeException
{
    public function __construct(string $message = 'Ce créneau vient d\'être réservé ou n\'est plus disponible. Merci d\'en choisir un autre.')
    {
        parent::__construct($message);
    }
}

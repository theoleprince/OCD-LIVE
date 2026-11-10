<?php

namespace App\Exceptions;

use RuntimeException;

/** Le vendeur a déjà une demande active (PENDING, UNDER_REVIEW ou APPROVED) pour ce compte TikTok. */
class DuplicateSubmissionException extends RuntimeException
{
    public function __construct(public readonly string $existingReference)
    {
        parent::__construct('Vous avez déjà une demande en cours pour ce compte TikTok (référence '.$existingReference.'). Merci d\'attendre son traitement avant d\'en soumettre une nouvelle.');
    }
}

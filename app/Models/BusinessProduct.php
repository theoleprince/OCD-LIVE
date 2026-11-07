<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessProduct extends Model
{
    protected $fillable = ['business_id', 'name', 'quantity_available', 'price_note'];

    protected function casts(): array
    {
        return ['quantity_available' => 'integer'];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}

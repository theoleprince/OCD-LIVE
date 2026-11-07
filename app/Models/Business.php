<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Business extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id', 'name', 'sector', 'description', 'offer_nature',
        'business_phone', 'business_address', 'social_links', 'logo_path',
        'is_food', 'food_preparation', 'food_conservation', 'food_presentation', 'is_demo',
    ];

    protected function casts(): array
    {
        return [
            'social_links' => 'array',
            'is_food' => 'boolean',
            'is_demo' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function products(): HasMany
    {
        return $this->hasMany(BusinessProduct::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}

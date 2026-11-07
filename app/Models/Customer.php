<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Customer extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'first_name', 'last_name', 'email', 'phone', 'whatsapp',
        'country', 'city', 'timezone', 'is_demo',
    ];

    protected function casts(): array
    {
        return ['is_demo' => 'boolean'];
    }

    public function businesses(): HasMany
    {
        return $this->hasMany(Business::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function getWhatsappUrlAttribute(): string
    {
        return 'https://wa.me/'.preg_replace('/\D+/', '', $this->whatsapp);
    }
}

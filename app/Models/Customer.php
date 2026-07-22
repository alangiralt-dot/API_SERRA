<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    public $timestamps = false;
    protected $table = 'customers';
    
    // Els atributs que es poden modificar des del formulari.
    protected $fillable = [
        'first_name', 
        'last_name', 
        'phone', 
        'street', 
        'address_number', 
        'address_floor', 
        'door', 
        'city_id',
        'postal_code'
    ];

    // customer ||--|| user
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'customer_id');
    }

    //cities ||--{ customers
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    //customers ||--{ orders
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }
}

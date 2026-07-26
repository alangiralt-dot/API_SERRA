<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Availability extends Model
{
    public $timestamps = false;
    protected $table = 'availabilities';

    //availabilities ||--{ child_products
    public function childProducts(): HasMany
    {
        return $this->hasMany(ChildProduct::class, 'availability_id');
    }
}

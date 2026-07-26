<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    public $timestamps = false;
    protected $table = 'units';

    //units ||--{ child_products
    public function childProducts(): HasMany
    {
        return $this->hasMany(ChildProduct::class, 'unit_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Status extends Model
{
    public $timestamps = false;
    protected $table = 'statuses';

    // statuses ||--{ orders
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'status_id');
    }
}

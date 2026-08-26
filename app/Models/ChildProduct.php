<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ChildProduct extends Model
{
    public $timestamps = false;
    protected $table = 'child_products';
    protected $guarded = [];

    //father_products ||--{ child_products
    public function fatherProduct(): BelongsTo
    {
        return $this->belongsTo(FatherProduct::class, 'father_product_id');
    }

    //availabilities ||--{ child_products
    public function availability(): BelongsTo
    {
        return $this->belongsTo(Availability::class, 'availability_id');
    }

    //units ||--{ child_products
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    //child_products ||--{ child_product_order }--|| orders
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'child_product_order', 'child_product_id', 'order_id')
                    ->withPivot('discount', 'quantity', 'sale_unit_price');
    }
}

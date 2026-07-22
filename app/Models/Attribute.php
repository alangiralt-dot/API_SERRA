<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Attribute extends Model
{
    public $timestamps = false;
    protected $table = 'attributes';

    //attributes ||--{ attribute_father_product }--|| father_products
    public function fatherProducts(): BelongsToMany
    {
        return $this->belongsToMany(
            FatherProduct::class, 
            'attribute_father_product', 
            'attribute_id', 
            'father_product_id'
        )->withPivot('value');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FatherProduct extends Model
{
    public $timestamps = false;
    protected $table = 'father_products';

    //categories ||--{ father_products
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    //father_products ||--{ child_products
    public function childProducts(): HasMany
    {
        return $this->hasMany(ChildProduct::class, 'father_product_id');
    }

    //father_products ||--{ attribute_father_product }--|| attributes
    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'attribute_father_product', 'father_product_id', 'attribute_id')
                    ->withPivot('value');
    }
}

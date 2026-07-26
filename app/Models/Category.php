<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    public $timestamps = false;
    protected $table = 'categories';

    //categories ||--{ categories
    public function parentCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'father_id');
    }

    //Una categoria pot tenir moltes subcategories filles
    public function subcategories(): HasMany
    {
        return $this->hasMany(Category::class, 'father_id');
    }

    //categories ||--{ father_products
    public function fatherProducts(): HasMany
    {
        return $this->hasMany(FatherProduct::class, 'category_id');
    }
}

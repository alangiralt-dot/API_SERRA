<?php

namespace App\Rules;

use App\Models\ChildProduct;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IsMultipleOfPack implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $childProductId = request()->input('id');
        $product = ChildProduct::find($childProductId);

        if ($product) {
            $quantity = $value;
            $pack = $product->pack;

            if ($quantity % $pack !== 0) {
                $fail("The quantity must be a multiple of the product pack ({$pack}).");
            }
        }
    }
}

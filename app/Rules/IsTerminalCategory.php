<?php

namespace App\Rules;

use App\Models\Category;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IsTerminalCategory implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $isParent = Category::where('father_id', $value)->exists();

        if ($isParent) {
            $fail("Only terminal categories are allowed to have products.");
        }
    }
}

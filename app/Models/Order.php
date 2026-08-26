<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;

class Order extends Model
{
    public $timestamps = false;
    protected $fillable = ['customer_id', 'code', 'status_id', 'date', 'order_availability', 'total_amount'];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $currentYear = Carbon::now()->year;
            $lastOrder = static::whereYear('date', $currentYear)
                ->orderBy('id', 'desc')
                ->first();
            if ($lastOrder) {
                $lastSequence = (int) substr($lastOrder->code, -5);
                $nextSequence = $lastSequence + 1;
            } else {
                $nextSequence = 1;
            }
            $order->code = 'SERRA-' . $currentYear . '-' . str_pad($nextSequence, 5, '0', STR_PAD_LEFT);
        });
    }

    // customers ||--{ orders
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    // statuses ||--{ orders
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    // orders ||--{ child_product_order }--|| child_products
    public function childProducts(): BelongsToMany
    {
        return $this->belongsToMany(ChildProduct::class, 'child_product_order', 'order_id', 'child_product_id')
            ->withPivot('discount', 'quantity', 'sale_unit_price', 'subtotal');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TrackedPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'price',
        'tracked_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'tracked_at' => 'datetime',
    ];

    /**
     * Get the product that this price belongs to.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope a query to only include prices for a specific product.
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope a query to order by tracked date descending.
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('tracked_at', 'desc');
    }

    /**
     * Scope a query to get recent price records.
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('tracked_at', '>=', now()->subDays($days));
    }
}

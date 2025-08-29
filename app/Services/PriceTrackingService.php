<?php

namespace App\Services;

use App\Models\Product;
use App\Models\TrackedPrice;
use Illuminate\Support\Facades\Log;

class PriceTrackingService
{
    /**
     * Track prices for all published products
     */
    public function trackAllProducts(): array
    {
        $products = Product::where('is_published', true)->get();
        $results = [
            'total' => $products->count(),
            'tracked' => 0,
            'unchanged' => 0,
            'errors' => 0,
        ];

        foreach ($products as $product) {
            try {
                $result = $this->trackProduct($product);
                if ($result['tracked']) {
                    $results['tracked']++;
                } else {
                    $results['unchanged']++;
                }
            } catch (\Exception $e) {
                $results['errors']++;
                Log::error("Failed to track price for product {$product->id}: " . $e->getMessage());
            }
        }

        return $results;
    }

    /**
     * Track price for a specific product
     */
    public function trackProduct(Product $product): array
    {
        try {
            $currentPrice = $this->getCurrentProductPrice($product);
            
            if ($currentPrice <= 0) {
                throw new \InvalidArgumentException("Invalid price calculated for product {$product->id}");
            }
            
            $lastTracked = TrackedPrice::where('product_id', $product->id)
                ->orderBy('tracked_at', 'desc')
                ->first();

            // Only track if price changed or if this is the first tracking
            if (!$lastTracked || abs($lastTracked->price - $currentPrice) >= 0.01) {
                $trackedPrice = TrackedPrice::create([
                    'product_id' => $product->id,
                    'price' => $currentPrice,
                    'tracked_at' => now(),
                ]);

                $change = $lastTracked ? $currentPrice - $lastTracked->price : 0;

                Log::info("Price tracked for product {$product->id}: {$currentPrice} (change: {$change})");

                return [
                    'tracked' => true,
                    'previous_price' => $lastTracked ? (float) $lastTracked->price : null,
                    'new_price' => $currentPrice,
                    'change' => round($change, 2),
                    'change_percent' => $lastTracked ? round(($change / $lastTracked->price) * 100, 2) : 0,
                    'tracked_price_id' => $trackedPrice->id,
                ];
            }

            return [
                'tracked' => false,
                'reason' => 'No price change detected',
                'current_price' => $currentPrice,
            ];
        } catch (\Exception $e) {
            Log::error("Failed to track price for product {$product->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get current price for a product
     * This method can be extended to fetch from external sources
     */
    private function getCurrentProductPrice(Product $product): float
    {
        // Start with the product's base price
        $basePrice = (float) $product->price;
        
        // In a real application, you might:
        // 1. Check external APIs for current market price
        // 2. Apply business logic for pricing rules
        // 3. Consider inventory levels, demand, etc.
        
        // For demonstration, we'll simulate minor price fluctuations
        return $this->simulatePriceFluctuation($basePrice);
    }

    /**
     * Simulate realistic price fluctuations for demonstration
     */
    private function simulatePriceFluctuation(float $basePrice): float
    {
        // Create small random variations (±1% to ±3%)
        $variationPercent = (rand(-300, 300) / 10000); // -0.03 to +0.03
        $variation = $basePrice * $variationPercent;
        
        $newPrice = $basePrice + $variation;
        
        // Ensure price doesn't go below 80% or above 120% of base price
        $newPrice = max($newPrice, $basePrice * 0.8);
        $newPrice = min($newPrice, $basePrice * 1.2);
        
        return round($newPrice, 2);
    }

    /**
     * Get price statistics for dashboard
     */
    public function getPriceStatistics(): array
    {
        $totalProducts = Product::whereHas('trackedPrices')->count();
        $totalRecords = TrackedPrice::count();
        $averagePrice = TrackedPrice::avg('price') ?? 0;
        $recentActivity = TrackedPrice::where('tracked_at', '>=', now()->subDays(7))->count();

        return [
            'totalProducts' => $totalProducts,
            'totalRecords' => $totalRecords,
            'averagePrice' => number_format($averagePrice, 2),
            'recentActivity' => $recentActivity,
        ];
    }

    /**
     * Get recent price changes with analysis
     */
    public function getRecentPriceChanges(int $limit = 10): array
    {
        return TrackedPrice::with('product')
            ->orderBy('tracked_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($price) {
                $previousPrice = TrackedPrice::where('product_id', $price->product_id)
                    ->where('tracked_at', '<', $price->tracked_at)
                    ->orderBy('tracked_at', 'desc')
                    ->first();

                $changeAmount = null;
                $changePercent = null;
                
                if ($previousPrice) {
                    $changeAmount = $price->price - $previousPrice->price;
                    $changePercent = ($changeAmount / $previousPrice->price) * 100;
                }

                return [
                    'id' => $price->id,
                    'product_name' => $price->product->name ?? 'Unknown Product',
                    'product_sku' => $price->product->sku ?? 'N/A',
                    'price' => number_format((float) $price->price, 2),
                    'tracked_at' => $price->tracked_at,
                    'change_amount' => $changeAmount ? round($changeAmount, 2) : null,
                    'change_percent' => $changePercent ? round($changePercent, 2) : null,
                    'trend' => $changeAmount ? ($changeAmount > 0 ? 'up' : 'down') : 'neutral',
                ];
            })
            ->toArray();
    }

    /**
     * Get price trend for a specific product
     */
    public function getProductPriceTrend(int $productId, int $days = 30): array
    {
        $prices = TrackedPrice::where('product_id', $productId)
            ->where('tracked_at', '>=', now()->subDays($days))
            ->orderBy('tracked_at', 'asc')
            ->get();

        return $prices->map(function ($price) {
            return [
                'date' => $price->tracked_at->format('Y-m-d'),
                'price' => (float) $price->price,
                'timestamp' => $price->tracked_at->timestamp,
            ];
        })->toArray();
    }
}

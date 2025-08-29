<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\TrackedPrice;
use App\Services\PriceTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PriceTrackerController extends Controller
{
    protected PriceTrackingService $priceTrackingService;

    public function __construct(PriceTrackingService $priceTrackingService)
    {
        $this->priceTrackingService = $priceTrackingService;
    }
    /**
     * Get all tracked prices
     */
    public function index()
    {
        $prices = TrackedPrice::with('product')
            ->latest()
            ->paginate(50);

        return response()->json($prices);
    }

    /**
     * Get price history for a specific product
     */
    public function getProductHistory($productId)
    {
        $prices = TrackedPrice::forProduct($productId)
            ->orderBy('tracked_at', 'desc')
            ->limit(100)
            ->get()
            ->map(function ($price) {
                return [
                    'id' => $price->id,
                    'product_id' => $price->product_id,
                    'price' => (float) $price->price, // Ensure it's a number
                    'tracked_at' => $price->tracked_at,
                ];
            });

        return response()->json($prices);
    }

    /**
     * Store a new tracked price
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'price' => 'required|numeric|min:0',
        ]);

        $trackedPrice = TrackedPrice::create([
            'product_id' => $request->product_id,
            'price' => $request->price,
            'tracked_at' => now(),
        ]);

        return response()->json($trackedPrice->load('product'), 201);
    }

    /**
     * Update a tracked price
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'price' => 'required|numeric|min:0',
        ]);

        $trackedPrice = TrackedPrice::findOrFail($id);
        $trackedPrice->update([
            'price' => $request->price,
        ]);

        return response()->json($trackedPrice->load('product'));
    }

    /**
     * Delete a tracked price
     */
    public function destroy($id)
    {
        $trackedPrice = TrackedPrice::findOrFail($id);
        $trackedPrice->delete();

        return response()->json(['message' => 'Price record deleted successfully']);
    }

    /**
     * Get dashboard statistics
     */
    public function getStats()
    {
        $stats = $this->priceTrackingService->getPriceStatistics();
        return response()->json($stats);
    }

    /**
     * Get products for the price tracker component
     */
    public function getProducts()
    {
        $products = Product::where('is_published', true)
            ->with('brand')
            ->orderBy('name')
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => (float) $product->price,
                    'sku' => $product->sku,
                    'brand_name' => $product->brand ? $product->brand->name : 'No Brand',
                ];
            });

        return response()->json($products);
    }

    /**
     * Get recent price changes with comparison to previous prices
     */
    public function getRecentChanges()
    {
        $recentChanges = $this->priceTrackingService->getRecentPriceChanges(10);
        return response()->json($recentChanges);
    }

    /**
     * Manually track current prices for all products
     */
    public function trackAllPrices()
    {
        $results = $this->priceTrackingService->trackAllProducts();
        
        return response()->json([
            'message' => 'Price tracking completed',
            'results' => $results,
        ]);
    }

    /**
     * Track price for a specific product
     */
    public function trackProductPrice(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);
        $result = $this->priceTrackingService->trackProduct($product);
        
        return response()->json([
            'message' => 'Price tracking completed for ' . $product->name,
            'result' => $result,
        ]);
    }

    /**
     * Get price trend for a specific product
     */
    public function getProductTrend($productId, Request $request)
    {
        $days = $request->get('days', 30);
        $trend = $this->priceTrackingService->getProductPriceTrend($productId, $days);
        
        return response()->json($trend);
    }

    /**
     * Fetch price from external source (placeholder)
     */
    public function fetchPrice(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'source_url' => 'nullable|url',
        ]);

        // This is a placeholder - you would implement actual price fetching logic here
        // For now, we'll return a mock response
        return response()->json([
            'message' => 'Price fetching not implemented yet',
            'product_id' => $request->product_id,
        ]);
    }
}

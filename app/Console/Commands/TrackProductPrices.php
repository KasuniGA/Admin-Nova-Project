<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\TrackedPrice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TrackProductPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prices:track {--product=* : Specific product IDs to track} {--all : Track all products}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Track current prices for products and store them in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting price tracking...');

        $productIds = $this->option('product');
        $trackAll = $this->option('all');

        if (!empty($productIds)) {
            $products = Product::whereIn('id', $productIds)->get();
        } elseif ($trackAll) {
            $products = Product::where('is_published', true)->get();
        } else {
            $this->error('Please specify either --product=ID or --all flag');
            return Command::FAILURE;
        }

        if ($products->isEmpty()) {
            $this->warn('No products found to track.');
            return Command::SUCCESS;
        }

        $tracked = 0;
        $errors = 0;

        foreach ($products as $product) {
            try {
                $this->trackProductPrice($product);
                $tracked++;
                $this->line("✓ Tracked price for: {$product->name}");
            } catch (\Exception $e) {
                $errors++;
                $this->error("✗ Failed to track price for {$product->name}: " . $e->getMessage());
            }
        }

        $this->info("\nPrice tracking completed!");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Products Processed', $products->count()],
                ['Successfully Tracked', $tracked],
                ['Errors', $errors],
            ]
        );

        return Command::SUCCESS;
    }

    /**
     * Track price for a specific product
     */
    private function trackProductPrice(Product $product)
    {
        // For now, we'll track the current product price from the database
        // In a real scenario, you might fetch from external APIs or web scraping
        
        $currentPrice = $this->getCurrentPrice($product);
        
        // Check if this price is different from the last tracked price
        $lastTrackedPrice = TrackedPrice::where('product_id', $product->id)
            ->orderBy('tracked_at', 'desc')
            ->first();

        if (!$lastTrackedPrice || abs($lastTrackedPrice->price - $currentPrice) >= 0.01) {
            TrackedPrice::create([
                'product_id' => $product->id,
                'price' => $currentPrice,
                'tracked_at' => now(),
            ]);

            if ($lastTrackedPrice) {
                $change = $currentPrice - $lastTrackedPrice->price;
                $changePercent = ($change / $lastTrackedPrice->price) * 100;
                $this->line("  Price changed: $" . number_format($change, 2) . " (" . number_format($changePercent, 1) . "%)");
            }
        } else {
            $this->line("  No price change detected");
        }
    }

    /**
     * Get current price for a product
     * This is where you'd implement external price fetching logic
     */
    private function getCurrentPrice(Product $product): float
    {
        // For demonstration, we'll add some realistic price variation
        // In production, you'd fetch from external sources
        
        $basePrice = (float) $product->price;
        
        // Add small random variation (±2%)
        $variation = (rand(-200, 200) / 10000) * $basePrice;
        $newPrice = $basePrice + $variation;
        
        return round(max($newPrice, $basePrice * 0.9), 2); // Don't drop below 90% of base price
    }

    /**
     * Example method for fetching price from external API
     * You would implement this based on your specific requirements
     */
    private function fetchPriceFromExternalSource(Product $product): ?float
    {
        try {
            // Example: Fetch from an e-commerce API
            // $response = Http::timeout(10)->get("https://api.example.com/products/{$product->sku}/price");
            // 
            // if ($response->successful()) {
            //     return (float) $response->json('price');
            // }
            
            return null;
        } catch (\Exception $e) {
            $this->warn("Failed to fetch external price for {$product->name}: " . $e->getMessage());
            return null;
        }
    }
}

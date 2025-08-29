<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Brand;
use App\Models\TrackedPrice;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PriceTrackingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some brands if they don't exist
        $brands = [
            ['name' => 'Apple', 'website_url' => 'https://apple.com', 'industry' => 'Technology', 'is_published' => true],
            ['name' => 'Samsung', 'website_url' => 'https://samsung.com', 'industry' => 'Technology', 'is_published' => true],
            ['name' => 'Nike', 'website_url' => 'https://nike.com', 'industry' => 'Apparel', 'is_published' => true],
            ['name' => 'Sony', 'website_url' => 'https://sony.com', 'industry' => 'Technology', 'is_published' => true],
        ];

        foreach ($brands as $brandData) {
            Brand::firstOrCreate(['name' => $brandData['name']], $brandData);
        }

        // Create some products if they don't exist
        $products = [
            [
                'name' => 'iPhone 15 Pro',
                'slug' => 'iphone-15-pro',
                'description' => 'Latest iPhone with Pro features',
                'price' => 999.00,
                'sku' => 'IPH15PRO',
                'quantity' => 50,
                'is_published' => true,
                'brand_id' => Brand::where('name', 'Apple')->first()->id,
            ],
            [
                'name' => 'Samsung Galaxy S24',
                'slug' => 'samsung-galaxy-s24',
                'description' => 'Premium Android smartphone',
                'price' => 849.00,
                'sku' => 'SGS24',
                'quantity' => 30,
                'is_published' => true,
                'brand_id' => Brand::where('name', 'Samsung')->first()->id,
            ],
            [
                'name' => 'Nike Air Jordan 1',
                'slug' => 'nike-air-jordan-1',
                'description' => 'Classic basketball sneakers',
                'price' => 170.00,
                'sku' => 'NAJ1',
                'quantity' => 100,
                'is_published' => true,
                'brand_id' => Brand::where('name', 'Nike')->first()->id,
            ],
            [
                'name' => 'Sony WH-1000XM5',
                'slug' => 'sony-wh-1000xm5',
                'description' => 'Noise-canceling wireless headphones',
                'price' => 399.00,
                'sku' => 'SWH1000XM5',
                'quantity' => 25,
                'is_published' => true,
                'brand_id' => Brand::where('name', 'Sony')->first()->id,
            ],
            [
                'name' => 'iPad Pro 12.9"',
                'slug' => 'ipad-pro-129',
                'description' => 'Professional tablet with M2 chip',
                'price' => 1099.00,
                'sku' => 'IPADPRO129',
                'quantity' => 20,
                'is_published' => true,
                'brand_id' => Brand::where('name', 'Apple')->first()->id,
            ],
        ];

        foreach ($products as $productData) {
            Product::firstOrCreate(['sku' => $productData['sku']], $productData);
        }

        // Create price tracking history for each product
        $products = Product::all();
        
        foreach ($products as $product) {
            $this->createPriceHistory($product);
        }
    }

    /**
     * Create realistic price history for a product
     */
    private function createPriceHistory(Product $product): void
    {
        $basePrice = (float) $product->price;
        $currentDate = Carbon::now()->subDays(30); // Start 30 days ago
        
        // Generate price history with realistic fluctuations
        for ($i = 0; $i < 30; $i++) {
            // Create some price variation (±5% to ±15% from base price)
            $variationPercent = rand(-15, 15) / 100;
            $priceVariation = $basePrice * $variationPercent;
            $newPrice = max($basePrice + $priceVariation, $basePrice * 0.7); // Don't go below 70% of base price
            
            // Add some trending behavior
            if ($product->name === 'iPhone 15 Pro') {
                // iPhone tends to have small price drops over time
                $trendAdjustment = ($i * -2); // $2 drop per day
                $newPrice += $trendAdjustment;
            } elseif ($product->name === 'Nike Air Jordan 1') {
                // Sneakers might have more volatile pricing
                $volatility = rand(-10, 20); // More upward pressure
                $newPrice += $volatility;
            }

            // Ensure price stays reasonable
            $newPrice = max($newPrice, $basePrice * 0.5); // Never below 50% of original
            $newPrice = min($newPrice, $basePrice * 1.5); // Never above 150% of original

            TrackedPrice::create([
                'product_id' => $product->id,
                'price' => round($newPrice, 2),
                'tracked_at' => $currentDate->copy()->addHours(rand(6, 18)), // Random time during business hours
            ]);

            $currentDate->addDay();
        }

        // Add today's price (current product price)
        TrackedPrice::create([
            'product_id' => $product->id,
            'price' => $basePrice,
            'tracked_at' => Carbon::now(),
        ]);
    }
}

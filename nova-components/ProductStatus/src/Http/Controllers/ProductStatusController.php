<?php

namespace Acme\ProductStatus\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Product;

class ProductStatusController extends Controller
{
    public function getProducts()
    {
        $products = Product::select('id', 'name', 'sku', 'is_published')
            ->orderBy('name')
            ->get()
            ->map(function ($product) {
                // Convert boolean to status string for frontend
                $product->status = $product->is_published ? 'active' : 'inactive';
                return $product;
            });

        return response()->json($products);
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id',
            'status' => 'required|in:active,inactive'
        ]);

        // Convert status string to boolean
        $isPublished = $request->status === 'active';

        Product::whereIn('id', $request->product_ids)
            ->update(['is_published' => $isPublished]);

        return response()->json(['message' => 'Products updated successfully']);
    }

    public function updateSingle(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'status' => 'required|in:active,inactive'
        ]);

        // Convert status string to boolean
        $isPublished = $request->status === 'active';

        Product::where('id', $request->product_id)
            ->update(['is_published' => $isPublished]);

        return response()->json(['message' => 'Product updated successfully']);
    }
}

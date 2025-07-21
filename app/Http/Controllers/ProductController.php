<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    // GET /api/products
    public function index()
    {
        // $products = Cache::remember('products_all', 60, function () {
        //     return Product::all();
        // });
        $products = Product::query()->selectRaw('id, name, title')->get();
        // $products =  Product::selectRaw()
        return response()->json($products, 200);
    }

    // POST /api/products
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
        ]);

        $product = Product::create($request->all());
        return response()->json($product, 201);
    }

    // GET /api/products/{id}
    public function show($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        return response()->json($product, 200);
    }

    // PUT/PATCH /api/products/{id}
    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product->update($request->all());
        return response()->json($product, 200);
    }

    // DELETE /api/products/{id}
    public function destroy($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product->delete();
        return response()->json(['message' => 'Product deleted'], 200);
    }
}

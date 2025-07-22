<?php

namespace App\Http\Controllers;

use ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    // GET /api/products
    public function index()
    {
        $products = Product::query()->with('user')->selectRaw('id, name, title, user_id')->orderByDesc('id')->get();
        return ApiResponse::JsonResult($products, "Get product Lists");
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
        ]);
        $user = UserService::getAuthUser();
        $data = $request->only(['name', 'title']);
        $data['user_id'] = $user->id;
        $product = Product::create($data);
        return response()->json($product, 201);
    }

    // GET /api/products/{id}
    public function show($id)
    {
        $product = Product::find($id);
        if (!$product) return response()->json(['message' => 'Product not found'], 404);
        return response()->json($product, 200);
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        if (!$product) return response()->json(['message' => 'Product not found'], 404);
        $product->update($request->all());
        return response()->json($product, 200);
    }

    // DELETE /api/products/{id}
    public function destroy($id)
    {
        $product = Product::find($id);
        if (!$product)  return response()->json(['message' => 'Product not found'], 404);
        $product->delete();
        return response()->json(['message' => 'Product deleted'], 200);
    }

    public function showProductsForView()
    {
        $products = Product::query()
            ->with('user')
            ->select('id', 'name', 'title', 'user_id')
            ->orderByDesc('id')
            ->get();
        return view('products.index', compact('products'));
    }

}

<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->integer('per_page', 15);
        $products = Product::orderBy('id','desc')->paginate($perPage);
        return response()->json([
            'status' => true,
            'message' => 'Product retrieved successfully',
            'data' =>[
                'products'      => ProductResource::collection($products),
                'total'         => $products->total(),
                'per_page'      => $products->perPage(),
                'current_page'  => $products->currentPage(),
                'last_page'     => $products->lastPage(),
                'next_page_url' => $products->nextPageUrl(),
                'prev_page_url' => $products->previousPageUrl(),
            ]
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required|string|min:3|max:255',
            'description' => 'nullable|string',
            'stock' => 'required|integer|min:0',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'menssage' => 'All fields are required',
                'erros' => $validator->errors()
            ], 422);
        }

        $product = Product::create($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Product created successfully',
            'data' => new ProductResource($product)
        ],201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::find($id);
        if(!$product){
            return response()->json([
                'status' => false,
                'message' => 'Product no found!'
            ], 400);
        }

        return response()->json([
            'status' => true,
            'message' => 'Product retrieved successfully!',
            'data' => new ProductResource($product)
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required|string|min:3|max:255',
            'product_description' => 'nullable|string',
            'stock' => 'required|integer|min:0',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'menssage' => 'All fields are required',
                'erros' => $validator->errors()
            ], 422);
        }

        $product = Product::find($id);
        $product->update($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Product update successfully!',
            'data' => new ProductResource($product)
        ], 200);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::find($id);
        if(!$product){
            return response()->json([
                'status' => false,
                'message' => 'Product not found!'
            ], 404);
        }

        $product->delete();

        return response()->json([
            'status'=> true,
            'message' => 'Product delete successfully'
        ], 200);
    }
}

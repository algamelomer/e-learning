<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use App\Trait\FileUploadTrait;
class ProductCategoryController extends Controller
{   use FileUploadTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $productCategories = ProductCategory::paginate(10);

        if ($productCategories->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No product categories found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Product categories found',
            'data' => $productCategories
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:product_categories,slug',
            // 'image' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 422);
        }

        $validatedData = $validator->validated();
        $productCategory = ProductCategory::create($validatedData);


        $imagePath = $this->uploadImage($request, 'image');
        $validatedData['image'] = isset($imagePath) ? $imagePath : '';

        $productCategory = ProductCategory::create($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Product category created successfully',
            'data' => $productCategory
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductCategory $productCategory)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Product category found',
            'data' => $productCategory
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductCategory $productCategory)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:product_categories,slug,' . $productCategory->id,
            'image' => 'sometimes|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 422);
        }

        try {
            $validatedData = $validator->validated();
            $productCategory->update($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Product category updated successfully',
                'data' => $productCategory
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error updating product category: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating the product category.'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductCategory $productCategory)
    {
        try {
            $productCategory->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Product category deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting product category: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while deleting the product category.'
            ], 500);
        }
    }
}
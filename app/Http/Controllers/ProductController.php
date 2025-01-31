<?php

namespace App\Http\Controllers;

use App\Contracts\ProductServiceInterface;
use App\Exceptions\InvalidProductFilterException;
use App\Exceptions\ProductNotFoundException;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;

class ProductController extends Controller
{
    private ProductServiceInterface $productService;

    public function __construct(ProductServiceInterface $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Handles the product listing request.
     *
     * @param  ProductRequest  $request  The incoming request, containing the product-related validation rules.
     */
    public function index(ProductRequest $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        try {
            $validated = $request->validated();
            $products = $this->productService->getProducts($validated);

            return ProductResource::collection($products);
        } catch (ProductNotFoundException $e) {
            return response()->json(['error' => 'Not Found', 'message' => $e->getMessage()], 404);
        } catch (InvalidProductFilterException $e) {
            return response()->json(['error' => 'Bad Request', 'message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal Server Error', 'message' => 'Something went wrong.', $e->getMessage()], 500);
        }
    }
}

<?php

namespace App\Services;

use App\Contracts\ProductRepositoryInterface;
use App\Contracts\ProductServiceInterface;
use App\Exceptions\InvalidProductFilterException;
use App\Exceptions\ProductNotFoundException;
use App\Repositories\ProductRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductService implements ProductServiceInterface
{
    private ProductRepositoryInterface $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Retrieves a paginated list of products based on the validated request data.
     *
     * @param  array  $validatedRequest  The validated input request containing filter criteria for products.
     * @return LengthAwarePaginator A paginated list of products that match the filter criteria.
     *
     * @throws ProductNotFoundException If no products are found matching the criteria.
     * @throws InvalidProductFilterException If the filter criteria are invalid.
     * @throws \Exception If an unexpected error occurs.
     */
    public function getProducts(array $validatedRequest): LengthAwarePaginator
    {
        try {
            $products = $this->productRepository->getProducts($validatedRequest);

            if ($products->total() === 0) {
                throw new ProductNotFoundException('No products found matching the criteria.');
            }

            return $products;
        } catch (\InvalidArgumentException $e) {
            throw new InvalidProductFilterException($e->getMessage());
        } catch (ProductNotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new \Exception('An unexpected error occurred: '.$e->getMessage());
        }
    }
}

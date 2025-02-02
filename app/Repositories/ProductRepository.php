<?php

namespace App\Repositories;

use App\Contracts\ProductRepositoryInterface;
use App\Filters\ProductFilter;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class ProductRepository implements ProductRepositoryInterface
{
    /**
     * Retrieves a paginated list of products based on the provided filters.
     *
     * The method uses a cache mechanism to store the results for a specified duration,
     * reducing the number of repetitive database queries. It applies filters to the product
     * query and includes associated category data.
     *
     * @param  array  $filters  An array of filters to apply to the product query.
     * @param  int  $perPage  Number of products to display per page. Defaults to 10.
     * @return LengthAwarePaginator A paginated result set of filtered products.
     */
    public function getProducts(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $cacheKey = $this->generateCacheKey($filters, $perPage);

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($filters, $perPage) {
            $query = Product::with('category');
            $query = ProductFilter::apply($query, $filters);

            return $query->paginate($perPage);
        });
    }

    /**
     * Generates a unique cache key based on the provided filters and pagination settings.
     *
     * This method creates a cache key by hashing the serialised filters and per-page values,
     * ensuring consistency and uniqueness for identifying cached data.
     *
     * @param  array  $filters  An array of filters applied to the product query.
     * @param  int  $perPage  Number of products to display per page.
     * @return string A unique string representing the cache key.
     */
    private function generateCacheKey(array $filters, int $perPage): string
    {
        return 'products:' . md5(json_encode($filters) . "|perPage:$perPage");
    }

}

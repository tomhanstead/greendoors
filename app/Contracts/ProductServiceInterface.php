<?php

namespace App\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductServiceInterface
{
    /**
     * Retrieves a paginated list of products based on the validated request parameters.
     *
     * @param  array  $validatedRequest  An array of request parameters that have been validated.
     * @return LengthAwarePaginator Returns a paginator instance containing the products.
     */
    public function getProducts(array $validatedRequest): LengthAwarePaginator;
}

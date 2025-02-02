<?php

namespace App\Filters;

use App\Contracts\ProductFilterInterface;
use Illuminate\Database\Eloquent\Builder;

class ProductFilter implements ProductFilterInterface
{
    /**
     * Applies filters to the query based on the given parameters.
     *
     * - Filters by category if a category filter is provided.
     * - Searches for products by name if a search filter is provided.
     * - Sorts the query by price in the specified order ('asc' or 'desc'),
     *   defaulting to ascending order if no valid sort order is provided.
     *
     * @param  Builder  $query  The query builder instance to which filters are applied.
     * @param  array  $filters  The array of filters containing optional keys:
     *                          'category', 'search', and 'sort'.
     * @return Builder The modified query builder with the applied filters.
     */
    public static function apply(Builder $query, array $filters): Builder
    {
        // Filter by category
        if (! empty($filters['category'])) {
            $query->whereHas('category', function ($q) use ($filters) {
                $q->where('name', $filters['category']);
            });
        }

        // Search by product name
        if (! empty($filters['search'])) {
            $query->where('name', 'like', '%'.$filters['search'].'%');
        }

        // Sort by price (default to ascending)
        $sortOrder = $filters['sort'] ?? 'asc';
        if (in_array($sortOrder, ['asc', 'desc'])) {
            $query->orderBy('price', $sortOrder);
        }

        return $query;
    }
}

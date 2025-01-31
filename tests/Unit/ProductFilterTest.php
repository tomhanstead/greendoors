<?php

namespace Tests\Unit;

use App\Filters\ProductFilter;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductFilterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Setup test data.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create categories
        $electronics = Category::factory()->create(['name' => 'Electronics']);
        $furniture = Category::factory()->create(['name' => 'Furniture']);

        // Create products in each category
        Product::factory()->create(['name' => 'Laptop', 'price' => 1500, 'category_id' => $electronics->id]);
        Product::factory()->create(['name' => 'Phone', 'price' => 800, 'category_id' => $electronics->id]);
        Product::factory()->create(['name' => 'Table', 'price' => 300, 'category_id' => $furniture->id]);
    }

    /**
     * Test filtering products by category.
     */
    public function test_filter_by_category()
    {
        $query = Product::query();
        $filteredQuery = ProductFilter::apply($query, ['category' => 'Electronics']);

        $products = $filteredQuery->orderBy('id')->get();

        $this->assertCount(2, $products);

        $productNames = $products->pluck('name')->toArray();
        sort($productNames);

        $expectedNames = ['Laptop', 'Phone'];
        sort($expectedNames);

        $this->assertEquals($expectedNames, $productNames);
    }

    /**
     * Test searching for products by name.
     */
    public function test_search_by_name()
    {
        $query = Product::query();
        $filteredQuery = ProductFilter::apply($query, ['search' => 'Phone']);

        $products = $filteredQuery->get();

        $this->assertCount(1, $products);
        $this->assertEquals('Phone', $products[0]->name);
    }

    /**
     * Test sorting products by price in ascending order.
     */
    public function test_sort_by_price_ascending()
    {
        $query = Product::query();
        $filteredQuery = ProductFilter::apply($query, ['sort' => 'asc']);

        $products = $filteredQuery->get();

        $this->assertCount(3, $products);
        $this->assertEquals('Table', $products[0]->name); // Cheapest
        $this->assertEquals('Phone', $products[1]->name);
        $this->assertEquals('Laptop', $products[2]->name); // Most expensive
    }

    /**
     * Test sorting products by price in descending order.
     */
    public function test_sort_by_price_descending()
    {
        $query = Product::query();
        $filteredQuery = ProductFilter::apply($query, ['sort' => 'desc']);

        $products = $filteredQuery->get();

        $this->assertCount(3, $products);
        $this->assertEquals('Laptop', $products[0]->name); // Most expensive
        $this->assertEquals('Phone', $products[1]->name);
        $this->assertEquals('Table', $products[2]->name); // Cheapest
    }

    /**
     * Test when no filters are applied.
     */
    public function test_no_filters_applied()
    {
        $query = Product::query();
        $filteredQuery = ProductFilter::apply($query, []);

        $products = $filteredQuery->get();

        $this->assertCount(3, $products);
    }
}

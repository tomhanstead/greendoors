<?php

namespace Tests\Feature;

use App\Contracts\ProductServiceInterface;
use App\Exceptions\InvalidProductFilterException;
use App\Exceptions\ProductNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    private $productServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock the ProductServiceInterface
        $this->productServiceMock = Mockery::mock(ProductServiceInterface::class);

        $this->app->instance(ProductServiceInterface::class, $this->productServiceMock);
    }

    /**
     * Test successful product retrieval.
     */
    public function test_index_returns_paginated_products()
    {
        $mockProducts = collect([(object) [
            'id' => 1,
            'name' => 'Phone',
            'price' => 499.99,
        ]]);
        $mockPaginator = new LengthAwarePaginator(
            $mockProducts,
            1,
            10
        );

        $this->productServiceMock
            ->shouldReceive('getProducts')
            ->once()
            ->andReturn($mockPaginator);

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [['id', 'name', 'price']],
                'links',
                'meta',
            ]);
    }

    /**
     * Test when no products are found.
     */
    public function test_index_returns_404_when_no_products_found()
    {
        // Mock service throwing ProductNotFoundException
        $this->productServiceMock
            ->shouldReceive('getProducts')
            ->once()
            ->andThrow(new ProductNotFoundException('No products found matching the criteria.'));

        $response = $this->getJson('/api/products');

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'Not Found',
                'message' => 'No products found matching the criteria.',
            ]);
    }

    /**
     * Test when an invalid filter is used.
     */
    public function test_index_returns_400_for_invalid_filter()
    {
        $response = $this->getJson('/api/products?invalid_filter=test');

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'Invalid filter applied.',
                'message' => 'Invalid filter(s) applied: invalid_filter',
            ]);
    }

    /**
     * Test when an unexpected error occurs.
     */
    public function test_index_returns_500_for_unexpected_errors()
    {
        // Mock service throwing a generic exception
        $this->productServiceMock
            ->shouldReceive('getProducts')
            ->once()
            ->andThrow(new \Exception('Database connection lost'));

        $response = $this->getJson('/api/products');

        $response->assertStatus(500)
            ->assertJson([
                'error' => 'Internal Server Error',
                'message' => 'Something went wrong.',
            ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}

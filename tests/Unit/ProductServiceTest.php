<?php

namespace Tests\Unit;

use App\Exceptions\InvalidProductFilterException;
use App\Exceptions\ProductNotFoundException;
use App\Repositories\ProductRepository;
use App\Services\ProductService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Mockery;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    private ProductService $productService;

    private $productRepositoryMock;

    /**
     * Setup the test dependencies.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Mock the ProductRepository
        $this->productRepositoryMock = Mockery::mock(ProductRepository::class);

        // Inject the mock into ProductService
        $this->productService = new ProductService($this->productRepositoryMock);
    }

    /**
     * Test successful product retrieval.
     */
    public function test_get_products_returns_paginated_list()
    {
        // Mocking the LengthAwarePaginator
        $mockPaginator = Mockery::mock(LengthAwarePaginator::class);
        $mockPaginator->shouldReceive('total')->andReturn(5);

        // Expect repository call and return mock data
        $this->productRepositoryMock
            ->shouldReceive('getProducts')
            ->once()
            ->andReturn($mockPaginator);

        // Execute the service method
        $result = $this->productService->getProducts(['category' => 'electronics']);

        // Assertions
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(5, $result->total());
    }

    /**
     * Test when no products are found.
     */
    public function test_get_products_throws_product_not_found_exception()
    {
        // Mock empty LengthAwarePaginator (total=0)
        $mockPaginator = Mockery::mock(LengthAwarePaginator::class);
        $mockPaginator->shouldReceive('total')->andReturn(0);

        $this->productRepositoryMock
            ->shouldReceive('getProducts')
            ->once()
            ->andReturn($mockPaginator);

        // Expect exception when no products are found
        $this->expectException(ProductNotFoundException::class);
        $this->expectExceptionMessage('No products found matching the criteria.');

        $this->productService->getProducts(['category' => 'nonexistent']);
    }

    /**
     * Test invalid filter usage.
     */
    public function test_get_products_throws_invalid_product_filter_exception()
    {
        // Mock repository throwing an InvalidArgumentException
        $this->productRepositoryMock
            ->shouldReceive('getProducts')
            ->once()
            ->andThrow(new \InvalidArgumentException('Invalid filter applied'));

        // Expect InvalidProductFilterException
        $this->expectException(InvalidProductFilterException::class);
        $this->expectExceptionMessage('Invalid filter applied');

        $this->productService->getProducts(['invalid_filter' => 'test']);
    }

    /**
     * Test unexpected errors.
     */
    public function test_get_products_throws_generic_exception()
    {
        // Mock repository throwing a generic Exception
        $this->productRepositoryMock
            ->shouldReceive('getProducts')
            ->once()
            ->andThrow(new \Exception('Database connection lost'));

        // Expect Exception
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Database connection lost');

        $this->productService->getProducts(['category' => 'electronics']);
    }

    /**
     * Cleanup mocks after each test.
     */
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}

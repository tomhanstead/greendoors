[![Laravel Version](https://img.shields.io/badge/Laravel-^11.0-red.svg?style=flat&logo=laravel)](https://laravel.com)
[![PHP Version](https://img.shields.io/badge/PHP-^8.3-777BB4.svg?style=flat&logo=php)](https://www.php.net/)

## Table of Contents
- [Overview](#overview)
- [Features](#features)
- [Getting Started](#getting-started)
  - [Prerequisites](#prerequisites)
- [Installation & Setup](#-installation--setup)
- [Testing](#testing-)
- [PSR-12](#psr-12)
- [Static Analysis (LaraStan)](#static-analysis-larastan)
- [Rate Limiting](#rate-limiting-60-requests-per-minute)
- [Backend Design and Architecture](#backend-design-and-architecture)
  - [SOLID Principles in the Application](#solid-principles-in-the-application)
    - [Single Responsibility Principle (SRP)](#single-responsibility-principle-srp)
    - [Open/Closed Principle (OCP)](#openclosed-principle-ocp)
    - [Dependency Inversion Principle (DIP)](#dependency-inversion-principle-dip)
  - [Architecture Overview](#architecture-overview)
    - [Controller Layer (API Handling)](#controller-layer-api-handling)
    - [Service Layer (Business Logic)](#service-layer-business-logic)
    - [Repository Layer (Database Interaction)](#repository-layer-database-interaction)
    - [Filter Layer](#filter-layer)
    - [Exception Handling Layer](#exception-handling-layer)
    - [Tests](#tests)
      - [ProductController Feature Tests](#productcontroller-feature-tests)
      - [Product Filter Unit Tests](#product-filter-unit-tests)
      - [ProductServiceTest - Unit Tests](#productservicetest---unit-tests)

## Overview
This is a **clean, efficient, and scalable Laravel API** for managing product listings. It follows **SOLID principles**, utilises **Laravel Factories & Seeders**, and implements **exception handling and request validation**.

## Features
- **Product Listing with Filtering & Pagination**
- **Eloquent Relationships for Categories & Products**
- **SOLID Principles**
- **Factory-Based Database Seeding**
- **Structured Exception Handling**
- **Adheres to Laravel Best Practices**

## Getting started

### Prerequisites

Before you begin, ensure your local environment meets the following requirements:

- **PHP >= 8.3**
- **Composer** (PHP dependency management)
- A database (I use MySQL, but SQLlite and others will work)



## 🚀 Installation & Setup
1. **Clone the repository**:
    ```bash
    git clone https://github.com/tomhanstead/greendoors.git
   cd greendoors
    ```
2. **Install dependencies**:
    ```bash
    composer install
    ```
3. **Create a `.env` file**:
    ```bash
    cp .env.example .env
    ```
   - **Update the `.env` file** with your database credentials:
    ```bash
    DB_CONNECTION=mysql
    DB_HOST=
    DB_PORT=
    DB_DATABASE=
    DB_USERNAME=
    DB_PASSWORD=
   ```
   
4. **Generate an application key**:
    ```bash
    php artisan key:generate
    ```
5. **Run the database migrations**:
    ```bash
    php artisan migrate
    ```
6. **Seed the database**:
    ```bash
    php artisan db:seed
    ```
7. **Start the Laravel server**:
    ```bash
    php artisan serve
    ```
8. **Visit the API in Postman**:
    ```
    http://localhost:8000/api/products
    ```
9. **Run the tests**:
    ```bash
    php artisan test
    ```

## Testing 
To run the tests, use the following command:
```bash
php artisan test
```

## PSR-12
This assignment follows the PSR-12 coding style, ensuring consistent code styling. I used Laravel Pint to check and fix code styles.
```bash
  ./vendor/bin/pint // you can also use --fix to automatically fix any issues.
```

## Static Analysis (LaraStan)
This project uses **LaraStan** for static analysis to ensure code quality and identify potential issues.
```bash
  ./vendor/bin/phpstan analyse
```

## Rate Limiting (60 requests per minute)
This project implements **rate limiting** to prevent abuse and ensure fair usage of the API. The rate limit is set to **60 requests per minute**.
```php
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});
```


## Backend Design and Architecture

This project follows **SOLID principles** and a **layered architecture** to ensure maintainability, scalability, and testability.

---

### **SOLID Principles in the Application**

#### **Single Responsibility Principle (SRP)**
- Each class is responsible for **only one** specific function:
    - **`ProductService`** handles **business logic** for retrieving products.
    - **`ProductRepository`** is responsible for **database interactions**.
    - **`ProductFilter`** applies filtering logic, keeping concerns separate.
    - **`ProductController`** acts only as a request handler.

#### **Open/Closed Principle (OCP)**
- The system is **open for extension but closed for modification**:
    - New filtering methods can be added to **`ProductFilter`** without modifying existing code.
    - New data sources (e.g., API, caching layer) can be integrated into **`ProductRepository`** without modifying other parts of the system.

#### **Liskov Substitution Principle (LSP)**
- **`ProductServiceInterface`** defines a contract for retrieving products, allowing **swappable implementations**.
- **`ProductService`** implements this interface, ensuring that any class implementing `ProductServiceInterface` can be substituted without breaking the code. 

#### **Dependency Inversion Principle (DIP)**
- **High-level modules do not depend on low-level modules**; both rely on **abstractions**:
    - **`ProductService`** interacts with **`ProductRepositoryInterface`**, making the database implementation swappable.
    - This enables **dependency injection**, allowing for better **testability** and flexibility.

---

### **Architecture Overview**
The backend is structured in a **layered approach**, ensuring clear separation of concerns.

### **Controller Layer (API Handling)**
- Handles **HTTP requests** and delegates processing to services.
- Uses **request validation** via `ProductRequest`.

 **Example: `ProductController.php`**
```php
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
```

### **Service Layer (Business Logic)**
- Encapsulates business rules and prevents logic from leaking into controllers.
- Calls repositories for data fetching and filters for query modifications.

    **Example: `ProductService.php`**
    ```php
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
  ```


### **Repository Layer (Database Interaction)**
- Abstracts database interactions from business logic
- Implements **`ProductRepositoryInterface`** for **dependency inversion**.
- Uses `Cache` for **caching** and **improving performance**.
- Uses Eloquent to fetch filtered products

**Example ProductRepository.php**
```php
   public function getProducts(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $cacheKey = $this->generateCacheKey($filters, $perPage);

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($filters, $perPage) {
            $query = Product::with('category');
            $query = ProductFilter::apply($query, $filters);

            return $query->paginate($perPage);
        });
    }
```

### **Filter Layer**
- Keeps filtering logic separate, making the ProductService more readable

**Example ProductFilter.php**
```php
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
```

### **Exception Handling Layer**
- Uses custom exceptions for better error messages.

**Example: ProductNotFoundException.php**
```php
public function render($request): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'error' => 'Product not found.',
            'message' => $this->getMessage(),
        ], Response::HTTP_NOT_FOUND);
    }
```
**Example: InvalidProductFilterException.php**
```php
  public function render($request): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'error' => 'Invalid filter applied.',
            'message' => $this->getMessage(),
        ], Response::HTTP_BAD_REQUEST);
    }
```

## Tests

### ProductController Feature Tests
### Overview
This test suite verifies the **API behavior** of `ProductController`, ensuring:
- **Successful product retrieval**
- **Handling of missing products (404)**
- **Validation of invalid filters (400)**
- **Unexpected error handling (500)**

The tests **mock the ProductServiceInterface** using **Mockery**, ensuring **unit-test-like isolation** while maintaining API-level validation.
### **Test Cases**
### **Successful Product Retrieval**
**Test:** `test_index_returns_paginated_products`
- Mocks `getProducts()` to return a **paginated product list**.
- API request to `/api/products` **returns HTTP 200**.

- **Expected Response:**
```json
{ "data": [{ "id": 1, "name": "Phone", "price": 499.99 }], "links": { ... }, "meta": { ... } }
```

### **No Products Found (404)**
**Test:** `test_index_returns_404_when_no_products_found`
- Mocks `getProducts()` to throw `ProductNotFoundException`.
- API request to `/api/products` **returns HTTP 404**.

- **Expected Response:**
```json
{ "error": "Not Found", "message": "No products found matching the criteria." }
```

### **Invalid Filter Handling (400)**
**Test:** `test_index_returns_400_for_invalid_filter`
- Mocks `getProducts()` to throw `InvalidProductFilterException`.
- API request to `/api/products?invalid_filter=test` **returns HTTP 400**.

- **Expected Response:**
```json
{ "error": "Invalid filter applied.", "message": "Invalid filter(s) applied: invalid_filter" }
```

### **Unexpected errors (500)**
**Test:** `test_index_returns_500_for_unexpected_errors`
- Mocks `getProducts()` throw a generic `Exception`.
- API request to `/api/products` **returns HTTP 500**.

- **Expected Response:**
```json
{ "error": "Internal Server Error", "message": "Something went wrong." }
```

### Product Filter Unit Tests
### Overview
This test suite verifies that **`ProductFilter`** correctly applies **category filtering, search functionality, and sorting** on the `Product` model.
- **Filtering by category returns only relevant products.**
- **Searching for product names returns correct results.**
- **Sorting by price (ascending & descending) works as expected.**
- **No filters applied returns all products.**

Each test is isolated using **`RefreshDatabase`** to ensure consistent results.
### **Test Cases**
### **Filter by Category**
**Test:** `test_filter_by_category`
- Mocks `getProducts()` to return a **paginated product list**.
- API request to `/api/products` **returns HTTP 200**.

- **Expected Response:**
```json
{ "data": [{ "id": 1, "name": "Phone", "price": 499.99 }], "links": { ... }, "meta": { ... } }
```

### **No Products Found (404)**
**Test:** `test_index_returns_404_when_no_products_found`
- Mocks `getProducts()` to throw `ProductNotFoundException`.
- API request to `/api/products` **returns HTTP 404**.

- **Expected Response:**
```json
{ "error": "Not Found", "message": "No products found matching the criteria." }
```

### **Invalid Filter Handling (400)**
**Test:** `test_index_returns_400_for_invalid_filter`
- Mocks `getProducts()` to throw `InvalidProductFilterException`.
- API request to `/api/products?invalid_filter=test` **returns HTTP 400**.

- **Expected Response:**
```json
{ "error": "Invalid filter applied.", "message": "Invalid filter(s) applied: invalid_filter" }
```

### **Unexpected Filter Handling (500)**
**Test:** `test_index_returns_500_for_unexpected_errors`
- Mocks `getProducts()` throw a generic `Exception`.
- API request to `/api/products` **returns HTTP 500**.

- **Expected Response:**
```json
{ "error": "Internal Server Error", "message": "Something went wrong." }
```



**Test Cases and Implementation** **1. Filtering Products by Category** **Test:**  `test_filter_by_category`
- Applies the filter `['category' => 'Electronics']` to the product query.

- Expects **only two products**  (`Laptop` and `Phone`) to be returned.


```php
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
```


---

**2. Searching for Products by Name** **Test:**  `test_search_by_name`
- Applies the filter `['search' => 'Phone']` to the product query.

- Expects **only one product**  (`Phone`) to be returned.


```php
public function test_search_by_name()
{
    $query = Product::query();
    $filteredQuery = ProductFilter::apply($query, ['search' => 'Phone']);

    $products = $filteredQuery->get();

    $this->assertCount(1, $products);
    $this->assertEquals('Phone', $products[0]->name);
}
```


---

**3. Sorting Products by Price (Ascending)** **Test:**  `test_sort_by_price_ascending`
- Applies the filter `['sort' => 'asc']` to the product query.

- Expects products to be sorted **from cheapest to most expensive** .


```php
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
```


---

**4. Sorting Products by Price (Descending)** **Test:**  `test_sort_by_price_descending`
- Applies the filter `['sort' => 'desc']` to the product query.

- Expects products to be sorted **from most expensive to cheapest** .


```php
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
```


---

**5. When No Filters Are Applied** **Test:**  `test_no_filters_applied`
- Applies **no filters**  to the product query.

- Expects **all products**  to be returned.


```php
public function test_no_filters_applied()
{
    $query = Product::query();
    $filteredQuery = ProductFilter::apply($query, []);

    $products = $filteredQuery->get();

    $this->assertCount(3, $products);
}
```

## ProductServiceTest - Unit Tests

### Overview
This test suite verifies the behavior of the `ProductService` class, ensuring that it correctly interacts with the `ProductRepository` to retrieve and process product data. It focuses on handling **valid product retrieval, missing products, invalid filters, and unexpected errors** .The tests use **Mockery**  to mock the `ProductRepository` and isolate `ProductService` from database interactions, ensuring unit-test-like behavior.

---

**Test Cases and Implementation** **1. Successful Product Retrieval** 
- **Test:**  `test_get_products_returns_paginated_list`
- Mocks the `ProductRepository::getProducts()` method to return a **paginated product list** .

- Verifies that the result is an instance of `LengthAwarePaginator` and contains **5 total products** .


```php
public function test_get_products_returns_paginated_list()
{
    // Mocking the LengthAwarePaginator
    $mockPaginator = Mockery::mock(LengthAwarePaginator::class);
    $mockPaginator->shouldReceive('total')->andReturn(5);

    $this->productRepositoryMock
        ->shouldReceive('getProducts')
        ->once()
        ->andReturn($mockPaginator);

    $result = $this->productService->getProducts(['category' => 'electronics']);

    $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    $this->assertEquals(5, $result->total());
}
```


---

2. No Products Found (Throws `ProductNotFoundException`)** **Test:**  `test_get_products_throws_product_not_found_exception`
- Mocks `ProductRepository::getProducts()` to return an **empty paginator**  (`total = 0`).

- Expects `ProductNotFoundException` with the message:
  *"No products found matching the criteria."*


```php
public function test_get_products_throws_product_not_found_exception()
{
    // Mock empty LengthAwarePaginator (total=0)
    $mockPaginator = Mockery::mock(LengthAwarePaginator::class);
    $mockPaginator->shouldReceive('total')->andReturn(0);

    $this->productRepositoryMock
        ->shouldReceive('getProducts')
        ->once()
        ->andReturn($mockPaginator);

    $this->expectException(ProductNotFoundException::class);
    $this->expectExceptionMessage('No products found matching the criteria.');

    $this->productService->getProducts(['category' => 'nonexistent']);
}
```


---

3. Invalid Filter Usage (Throws `InvalidProductFilterException`)** **Test:**  `test_get_products_throws_invalid_product_filter_exception`
- Mocks `ProductRepository::getProducts()` to throw an `InvalidArgumentException`.

- Expects `InvalidProductFilterException` with the message:
  *"Invalid filter applied."*


```php
public function test_get_products_throws_invalid_product_filter_exception()
{
    // Mock repository throwing an InvalidArgumentException
    $this->productRepositoryMock
        ->shouldReceive('getProducts')
        ->once()
        ->andThrow(new \InvalidArgumentException('Invalid filter applied'));

    $this->expectException(InvalidProductFilterException::class);
    $this->expectExceptionMessage('Invalid filter applied');

    $this->productService->getProducts(['invalid_filter' => 'test']);
}
```


---

4. Unexpected Errors (Throws `Exception`)** **Test:**  `test_get_products_throws_generic_exception`
- Mocks `ProductRepository::getProducts()` to throw a **generic exception**  (e.g., database connection lost).

- Expects `Exception` with the message:
  *"Database connection lost."*


```php
public function test_get_products_throws_generic_exception()
{
    // Mock repository throwing a generic Exception
    $this->productRepositoryMock
        ->shouldReceive('getProducts')
        ->once()
        ->andThrow(new \Exception('Database connection lost'));

    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('Database connection lost');

    $this->productService->getProducts(['category' => 'electronics']);
}
```


---

**Test Setup** Each test initialises a mocked `ProductRepository`**  and injects it into the `ProductService`.

```php
protected function setUp(): void
{
    parent::setUp();

    $this->productRepositoryMock = Mockery::mock(ProductRepository::class);

    $this->productService = new ProductService($this->productRepositoryMock);
}
```


---

**Mock Cleanup** Each test ensures that **Mockery is properly closed**  after execution.

```php
protected function tearDown(): void
{
    Mockery::close();
    parent::tearDown();
}
```





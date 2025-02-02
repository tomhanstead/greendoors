<?php

namespace App\Contracts;

interface ProductRepositoryInterface
{
    public function getProducts(array $filters, int $perPage = 10);
}

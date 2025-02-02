<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface ProductFilterInterface
{
    public static function apply(Builder $query, array $filters);
}

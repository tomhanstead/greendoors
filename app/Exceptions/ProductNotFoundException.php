<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class ProductNotFoundException extends Exception
{
    /**
     * Create a new ProductNotFoundException instance.
     *
     * @return \Illuminate\Http\JsonResponse The JSON response containing the error message.
     */
    public function render($request): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'error' => 'Product not found.',
            'message' => $this->getMessage(),
        ], Response::HTTP_NOT_FOUND);
    }
}

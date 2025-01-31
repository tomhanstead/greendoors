<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class InvalidProductFilterException extends Exception
{
    /**
     * Create a new InvalidProductFilterException instance.
     *
     * @return \Illuminate\Http\JsonResponse The JSON response containing the error message.
     */
    public function render($request): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'error' => 'Invalid filter applied.',
            'message' => $this->getMessage(),
        ], Response::HTTP_BAD_REQUEST);
    }
}

<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

class CustomValidationException extends Exception
{
    protected $errors;

    public function __construct($errors)
    {
        $this->errors = $errors;
    }

    public function render($request)
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Campos inválidos',
            'errors' => $this->errors,
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}

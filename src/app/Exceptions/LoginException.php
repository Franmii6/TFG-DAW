<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Throwable;

class LoginException extends Exception
{
    public function __construct(
        string $message = "Error de Login",
        int $code = Response::HTTP_INTERNAL_SERVER_ERROR,
        ?Throwable $previous = null
    )
    {
        parent::__construct($message, $code, $previous);
    }

    public function render (Request $request) {
        if($request->isJson()) {
            return response()->json([
                'message' => $this->message,
            ], $this->code);
        }
    }
}

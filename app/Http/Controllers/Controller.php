<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public function customJsonResponse(string $message, $data = null, int $status = Response::HTTP_OK)
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
        ], $status);
    }
}

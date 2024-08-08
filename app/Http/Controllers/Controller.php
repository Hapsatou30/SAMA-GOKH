<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public static function customJsonResponse($message, $data, $code = 200) {
        return response()->json([
            "message" => $message,
            "data" => $data,
        ], $code);
    }
}

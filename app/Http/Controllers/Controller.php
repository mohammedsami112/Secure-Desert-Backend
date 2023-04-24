<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Send a Success JSON Response.
     *
     * @param  string  $message
     * @param  int  $code
     * @param  mixed  $payload
     * @return \Illuminate\Http\JsonResponse
     */
    public function respond(string $message = 'Success', int $code = 200, mixed $payload = [])
    {
        return response()->json([
            'code' => $code,
            'success' => true,
            'message' => $message,
            'payload' => $payload,
        ], $code);
    }

    /**
     * Send a Failure JSON Response.
     *
     * @param  string  $message
     * @param  int  $code
     * @param  mixed  $payload
     * @return \Illuminate\Http\JsonResponse
     */
    public function fail(string $message = 'Bad Request', int $code = 400, mixed $payload = [])
    {
        throw new HttpResponseException(response()->json([
            'code' => $code,
            'success' => false,
            'message' => $message,
            'payload' => $payload,
        ], $code));
    }
}

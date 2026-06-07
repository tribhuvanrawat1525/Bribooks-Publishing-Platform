<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ApiLogMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        try {

            $executionTime = round(
                (microtime(true) - $startTime) * 1000,
                2
            );

            $requestData = $request->all();

            if (isset($requestData['password'])) {
                $requestData['password'] = '********';
            }

            if (isset($requestData['password_confirmation'])) {
                $requestData['password_confirmation'] = '********';
            }


            $headers = $request->headers->all();

            if (isset($headers['authorization'])) {
                $headers['authorization'] = ['Bearer ********'];
            }


            $responseBody = $response->getContent();

            // Prevent huge logs
            if (strlen($responseBody) > 10000) {
                $responseBody = substr(
                    $responseBody,
                    0,
                    10000
                ) . '... [TRUNCATED]';
            }

            DB::table('api_logs')->insert([

                'user_id' => auth()->id(),

                'method' => $request->method(),

                'url' => $request->fullUrl(),

                'ip_address' => $request->ip(),

                'request_headers' => json_encode(
                    $headers,
                    JSON_UNESCAPED_UNICODE
                ),

                'request_body' => json_encode(
                    $requestData,
                    JSON_UNESCAPED_UNICODE
                ),

                'response_body' => $responseBody,

                'status_code' => $response->getStatusCode(),

                'execution_time_ms' => $executionTime,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        } catch (\Exception $e) {

            // Never break API because logging failed
        }

        return $response;
    }
}
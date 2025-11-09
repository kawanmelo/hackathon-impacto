<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowedOrigin = config('app.frontend_url', '*');

        if ($request->getMethod() === 'OPTIONS') {
            return response()
                ->noContent()
                ->withHeaders($this->corsHeaders($allowedOrigin));
        }

        $response = $next($request);

        foreach ($this->corsHeaders($allowedOrigin) as $header => $value) {
            $response->headers->set($header, $value);
        }

        return $response;
    }

    private function corsHeaders(string $origin): array
    {
        return [
            'Access-Control-Allow-Origin' => $origin,
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, Accept, Origin',
            'Access-Control-Allow-Credentials' => 'true',
            'Vary' => 'Origin',
        ];
    }
}

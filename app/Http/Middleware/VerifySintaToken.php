<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifySintaToken
{
    public function handle(Request $request, Closure $next)
    {
        // Token yang dikirim SINTA
        $token = $request->header('X-SINTA-TOKEN');

        // Token valid dari .env SIA
        $validToken = env('SINTA_API_TOKEN');

        if (!$validToken) {
    return response()->json([
        'status' => false,
        'message' => 'Missing SINTA_API_TOKEN in server',
    ], 401);
}
        if ($token !== $validToken) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized - Invalid SINTA token',
            ], 401);
        }

        return $next($request);
    }
}
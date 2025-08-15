<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ErrorHandlingMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $response = $next($request);
            
            // Log des requêtes lentes (plus de 2 secondes)
            if (defined('LARAVEL_START')) {
                $executionTime = microtime(true) - LARAVEL_START;
                if ($executionTime > 2.0) {
                    Log::warning('Requête lente détectée', [
                        'url' => $request->fullUrl(),
                        'method' => $request->method(),
                        'execution_time' => $executionTime,
                        'user_id' => auth()->id(),
                        'ip' => $request->ip(),
                    ]);
                }
            }
            
            return $response;
            
        } catch (Throwable $e) {
            // Log de l'erreur avec contexte
            Log::error('Erreur dans la requête', [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'user_id' => auth()->id(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Relancer l'exception pour que Laravel la gère normalement
            throw $e;
        }
    }
}

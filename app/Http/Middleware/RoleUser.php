<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Vérifie si l'utilisateur est authentifié
        if (!auth()->check()) {
            return response()->json([
                'message' => 'Accès non autorisé. Utilisateur non authentifié.'
            ], 401);
        }

        // Vérifie si l'utilisateur a le rôle requis
        if (auth()->user()->role !== $role) {
            return response()->json([
                'message' => 'Accès refusé. Permissions insuffisantes.'
            ], 403);
        }

        return $next($request);
    }
}
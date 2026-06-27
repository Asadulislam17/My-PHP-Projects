<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
{
    
    if (!$request->user()) {
        return redirect()->route('login');
    }

    
    if ($request->user()->role !== $role) {
        return match ($request->user()->role) {
            'admin'    => redirect()->route('admin.dashboard'),
            'agent'    => redirect()->route('agent.dashboard'),
            default    => redirect()->route('dashboard'), // customer এর জন্য
        };
    }

    return $next($request);
}

}

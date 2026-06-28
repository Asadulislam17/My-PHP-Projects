<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // ১. ইউজার লগইন না থাকলে লগইন পেজে পাঠান
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // ২. রোল না মিললে যার যার নিজের ড্যাশবোর্ডে ফেরত পাঠান
        if (auth()->user()->role !== $role) {
            return match (auth()->user()->role) {
                'admin'   => redirect()->route('admin.dashboard'),
                'teacher' => redirect()->route('teacher.dashboard'),
                'student' => redirect()->route('student.dashboard'),
                default   => redirect('/'),
            };
        }

        return $next($request);
    }
}

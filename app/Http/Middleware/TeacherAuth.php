<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;

class TeacherAuth
{
    public function handle($request, Closure $next)
    {
        if (!Session::has('teacher_logged_in') || Session::get('teacher_logged_in') !== true) {
            return redirect()->route('auth.login');
        }

        return $next($request);
    }
}
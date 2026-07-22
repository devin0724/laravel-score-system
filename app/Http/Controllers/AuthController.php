<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ]);

        if ($request->password === env('TEACHER_PASSWORD', '123456')) {
            Session::put('teacher_logged_in', true);
            Session::put('login_time', time());
            return redirect()->route('teacher.index');
        }

        return back()->with('error', '密码错误，请重试');
    }

    public function logout()
    {
        Session::forget('teacher_logged_in');
        Session::forget('login_time');
        return redirect()->route('auth.login');
    }
}
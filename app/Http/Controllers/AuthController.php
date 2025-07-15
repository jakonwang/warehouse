<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function showMobileLoginForm()
    {
        return view('auth.mobile-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            // 移动端登录成功后直接跳转到移动端首页
            return redirect()->intended('mobile');
        }

        return back()->withErrors([
            'username' => '用户名或密码错误',
        ])->withInput($request->only('username'));
    }

    public function adminLogin(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            // 后台管理登录成功后跳转到后台管理首页
            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'username' => '用户名或密码错误',
        ])->withInput($request->only('username'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('mobile.login');
    }
} 
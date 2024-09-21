<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthAdminRequest;

class AdminController extends Controller
{
    // Render Admin Home Page

    public function index()
    {
        return view('admin.dashboard');
    }

    public function login()
    {
        if (!auth()->guard('admin')->check()) {
            return view('login');
        }

        return redirect()->route('admin.index');
    }
    public function auth(AuthAdminRequest $request)
    {
        if ($request->validated()) {
            $remember_me = $request->has('remember_me') ? true : false;
            if (auth()->guard('admin')->attempt([
                'email' => $request->email,
                'password' => $request->password,
            ], $remember_me)) {
                return redirect()->route('admin.index');
            } else {
                return redirect()->route('admin.login')->with(['error' => 'These credentials do not match!']);
            }
        }
    }

    public function logout()
    {
        auth()->guard('admin')->logout();
        return redirect()->route('admin.login');
    }
}

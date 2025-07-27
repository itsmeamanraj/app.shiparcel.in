<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserAuthController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function userLogin(LoginUserRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if ($user->status == 1) {

                $user->logActivity($user, 'User logged in', 'login');

                return redirect()->route('dashboard');
            }

            Auth::logout();
            return back()->withErrors(['email' => 'Your account is inactive.']);
        }

        return back()->withErrors(['email' => 'Invalid credentials.']);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        Auth::logout();

        if ($user) {
            $user->logActivity($user, 'User logged out', 'logout');
        }

        return redirect()->route('admin-login');
    }
}

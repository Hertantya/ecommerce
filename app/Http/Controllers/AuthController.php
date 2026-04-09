<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // ── Show login form ────────────────────────────────
    public function showLogin()
    {
        return view('auth.login');
    }

    // ── Process login ──────────────────────────────────
    public function login(Request $request)
    {
        // Validate input
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);

        // attempt() checks email+password and creates the session
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate(); // prevent session fixation

            // Redirect based on role
            return auth()->user()->role === 'admin'
                ? redirect()->route('admin.dashboard')
                : redirect()->route('customer.dashboard');
        }

        // Back to login with error
        return back()->withErrors(['email' => 'Invalid email or password.'])->onlyInput('email');
    }

    // ── Show registration form ─────────────────────────
    public function showRegister()
    {
        return view('auth.register');
    }

    // ── Process registration (customers only) ──────────
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'phone'    => 'nullable|string|max:20',
            'address'  => 'nullable|string',
            'password' => 'required|min:6|confirmed', // needs password_confirmation field
        ]);

        // Create user with hashed password, always as 'customer'
        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'address'  => $request->address,
            'password' => Hash::make($request->password),
            'role'     => 'customer',
        ]);

        return redirect()->route('login')->with('success', 'Registration successful! Please login.');
    }

    // ── Logout ─────────────────────────────────────────
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controller untuk autentikasi pengguna
 * Menangani login, logout, dan pengaturan akun
 */
class AuthController extends Controller
{
    /**
     * Menampilkan halaman login
     */
    public function showLogin()
    {
        return view('login');
    }

    /**
     * Proses login pengguna
     */
    public function login(Request $request)
    {
        // Validasi input
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required']
        ]);

        // Coba autentikasi
        if (Auth::attempt($request->only('email', 'password'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            // Redirect berdasarkan role
            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard');
            }

            if ($user->role === 'user') {
                return redirect()->route('chat.index');
            }

            // Fallback jika role tidak valid
            Auth::logout();
            return back()->withErrors(['email' => 'Role pengguna tidak valid.']);
        }

        return back()->withErrors(['email' => 'Email atau password salah.']);
    }

    /**
     * Proses logout pengguna
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Mengambil data akun pengguna yang sedang login
     */
    public function getAccount()
    {
        $user = Auth::user();

        return response()->json([
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone ?? '',
        ]);
    }

    /**
     * Memperbarui data akun pengguna
     */
    public function updateAccount(Request $request)
    {
        // Validasi input
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . Auth::id(),
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|min:6',
        ]);

        $user = Auth::user();

        // Update data akun
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->phone = $request->input('phone');

        // Update password jika diisi
        if ($request->filled('password')) {
            $user->password = bcrypt($request->input('password'));
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Akun berhasil diperbarui!'
        ]);
    }
}

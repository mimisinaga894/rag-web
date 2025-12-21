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
            'username' => ['required', 'string'],
            'password' => ['required']
        ]);

        // Coba autentikasi
        if (Auth::attempt($request->only('username', 'password'))) {
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
            return back()->withErrors(['username' => 'Role pengguna tidak valid.']);
        }

        return back()->withErrors(['username' => 'Username atau password salah.']);
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
            'username' => $user->username,
            'name' => $user->name,
            'email' => $user->email ?? '',
            'nim_nidn' => $user->nim_nidn ?? '',
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
            'email' => 'nullable|email|unique:users,email,' . Auth::id(),
            'nim_nidn' => 'nullable|string|max:20',
            'password' => 'nullable|min:6',
        ]);

        $user = Auth::user();

        // Update data akun
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->nim_nidn = $request->input('nim_nidn');

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

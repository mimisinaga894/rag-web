{{--
Profil Admin
Halaman untuk mengelola profil admin
--}}
@extends('layouts.admin')

@section('title', 'Profil')
@section('header', 'Profil Saya')

@section('content')
    <div class="card-section">
        <h2 class="section-title">👤 Informasi Akun</h2>

        <form action="{{ route('admin.profile.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">Nama</label>
                <input type="text" name="name" value="{{ $user->name }}" required class="form-control"
                    style="width: 100%; padding: 12px; border: 1px solid #e5e7eb; border-radius: 10px;">
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">Email</label>
                <input type="email" name="email" value="{{ $user->email }}" required class="form-control"
                    style="width: 100%; padding: 12px; border: 1px solid #e5e7eb; border-radius: 10px;">
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">Telepon</label>
                <input type="text" name="phone" value="{{ $user->phone }}" class="form-control"
                    style="width: 100%; padding: 12px; border: 1px solid #e5e7eb; border-radius: 10px;"
                    placeholder="Opsional">
            </div>

            <hr style="margin: 30px 0; border: none; border-top: 1px solid #e5e7eb;">

            <h3 style="margin-bottom: 15px; font-size: 16px; color: #374151;">🔐 Ubah Password</h3>
            <p style="color: #64748b; font-size: 14px; margin-bottom: 15px;">Kosongkan jika tidak ingin mengubah password
            </p>

            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">Password Baru</label>
                <input type="password" name="password" class="form-control"
                    style="width: 100%; padding: 12px; border: 1px solid #e5e7eb; border-radius: 10px;"
                    placeholder="Min. 6 karakter">
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">Konfirmasi
                    Password</label>
                <input type="password" name="password_confirmation" class="form-control"
                    style="width: 100%; padding: 12px; border: 1px solid #e5e7eb; border-radius: 10px;"
                    placeholder="Ulangi password baru">
            </div>

            <button type="submit"
                style="width: 100%; background: linear-gradient(135deg, #0ea5e9, #0284c7); color: #fff; border: none; padding: 14px; border-radius: 12px; font-weight: 600; font-size: 16px; cursor: pointer;">
                💾 Simpan Perubahan
            </button>
        </form>
    </div>
@endsection

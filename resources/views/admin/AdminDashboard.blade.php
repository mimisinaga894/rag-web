{{--
Dashboard Admin
Menampilkan statistik dan daftar dokumen
--}}
@extends('layouts.admin')

@section('title', 'Dashboard')
@section('header', 'Dashboard Admin')

@section('content')
    {{-- Statistik --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-label">Total User</div>
            <div class="stat-value">{{ $totalUsers }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">👨‍💼</div>
            <div class="stat-label">Total Admin</div>
            <div class="stat-value">{{ $totalAdmins }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📄</div>
            <div class="stat-label">Total Dokumen</div>
            <div class="stat-value">{{ $documents->count() }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💬</div>
            <div class="stat-label">Total Chat</div>
            <div class="stat-value">{{ $totalChats }}</div>
        </div>
    </div>

    {{-- Daftar Dokumen --}}
    <div class="card-section">
        <h2 class="section-title">📁 Dokumen Terbaru</h2>

        @if($documents->count() > 0)
            <div style="overflow-x:auto;">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama File</th>
                            <th>Tipe</th>
                            <th>Ukuran</th>
                            <th>Tanggal Upload</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($documents->take(5) as $index => $doc)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><strong>{{ $doc->file_name }}</strong></td>
                                <td>{{ strtoupper($doc->file_type) }}</td>
                                <td>{{ number_format($doc->file_size / 1024 / 1024, 2) }} MB</td>
                                <td>{{ $doc->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($documents->count() > 5)
                <div style="text-align: center; margin-top: 20px;">
                    <a href="{{ route('admin.upload.index') }}" class="btn-primary-custom">
                        Lihat Semua Dokumen
                    </a>
                </div>
            @endif
        @else
            <div class="empty-state">
                <div style="font-size:60px;">📂</div>
                Belum ada dokumen yang diupload.
            </div>
        @endif
    </div>
@endsection

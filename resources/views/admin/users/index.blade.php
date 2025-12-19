{{--
Kelola User
Halaman untuk melihat dan menghapus user
--}}
@extends('layouts.admin')

@section('title', 'Kelola User')
@section('header', 'Kelola User')

@section('content')
    <div class="card-section">
        <h2 class="section-title">👥 Daftar User</h2>

        @if($users->count() > 0)
            <div style="overflow-x:auto;">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Terdaftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $index => $user)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><strong>{{ $user->name }}</strong></td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <span class="badge bg-primary">{{ ucfirst($user->role) }}</span>
                                </td>
                                <td>{{ $user->created_at->format('Y-m-d') }}</td>
                                <td>
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST"
                                        onsubmit="return confirmDelete(this, 'Hapus user {{ $user->name }}?');" style="margin:0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-delete">
                                            <i class="bi bi-trash"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($users->hasPages())
                <div style="margin-top: 20px;">
                    {{ $users->links() }}
                </div>
            @endif
        @else
            <div class="empty-state">
                <div style="font-size:60px;">👤</div>
                Belum ada user terdaftar.
            </div>
        @endif
    </div>
@endsection

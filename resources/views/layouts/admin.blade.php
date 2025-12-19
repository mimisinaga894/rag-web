{{--
Layout Admin - Template utama untuk halaman admin
Berisi sidebar, header, dan slot untuk konten
--}}
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') - SIAssist</title>

    {{-- Fonts & Icons --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* ========== RESET & BASE ========== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 50%, #0369a1 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ========== SIDEBAR ========== */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: 270px;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            padding: 25px 20px;
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            border-right: 1px solid rgba(14, 165, 233, 0.2);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e0f2fe;
        }

        .sidebar-logo {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.4);
        }

        .sidebar-title {
            font-size: 20px;
            font-weight: 700;
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .sidebar-menu {
            list-style: none;
            margin: 0;
            padding: 0;
            flex-grow: 1;
        }

        .sidebar-menu li {
            margin-bottom: 5px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: #334155;
            text-decoration: none;
            transition: all .3s;
            font-size: 14px;
            font-weight: 500;
            border-radius: 10px;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.1), rgba(2, 132, 199, 0.1));
            color: #0ea5e9;
            transform: translateX(5px);
        }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            border: none;
            border-radius: 10px;
            color: #fff;
            width: 100%;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all .3s;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        }

        .logout-btn:hover {
            transform: translateY(-2px);
        }

        /* ========== MAIN CONTENT ========== */
        .main-content {
            margin-left: 270px;
            padding: 30px;
            min-height: 100vh;
        }

        .top-header {
            background: rgba(255, 255, 255, 0.98);
            padding: 25px 30px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid rgba(14, 165, 233, 0.2);
        }

        .header-title {
            font-size: 26px;
            font-weight: 700;
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 20px;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.4);
        }

        /* ========== KOMPONEN UMUM ========== */
        .card-section {
            background: #fff;
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
            border: 1px solid rgba(14, 165, 233, 0.2);
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 20px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* ========== STATISTIK ========== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: #fff;
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
            border: 1px solid rgba(14, 165, 233, 0.2);
            transition: all .3s;
        }

        .stat-card:hover {
            transform: translateY(-4px);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            margin-bottom: 15px;
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.1), rgba(2, 132, 199, 0.1));
        }

        .stat-label {
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .stat-value {
            font-size: 34px;
            font-weight: 700;
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* ========== TABEL ========== */
        .table-custom {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
        }

        .table-custom th,
        .table-custom td {
            padding: 14px;
            text-align: left;
        }

        .table-custom th {
            font-size: 12px;
            color: #0284c7;
            text-transform: uppercase;
            border-bottom: 2px solid rgba(14, 165, 233, 0.2);
        }

        .table-custom td {
            border-bottom: 1px solid rgba(14, 165, 233, 0.1);
            font-size: 14px;
            color: #334155;
        }

        .table-custom tr:hover {
            background: rgba(14, 165, 233, 0.05);
        }

        /* ========== TOMBOL ========== */
        .btn-primary-custom {
            background: linear-gradient(135deg, #0ea5e9, #0284c7);
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all .3s;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.4);
        }

        .btn-delete {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #fff;
            border: none;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .btn-delete:hover {
            transform: scale(1.05);
        }

        .btn-download {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff;
            border: none;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .btn-bulk-delete {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all .3s;
        }

        .btn-bulk-delete:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* ========== UPLOAD BOX ========== */
        .upload-box {
            display: block;
            width: 100%;
            border: 2px dashed rgba(14, 165, 233, 0.3);
            border-radius: 16px;
            padding: 50px;
            text-align: center;
            cursor: pointer;
            background: rgba(14, 165, 233, 0.05);
            transition: 0.3s;
        }

        .upload-box:hover {
            border-color: #0ea5e9;
            background: rgba(14, 165, 233, 0.1);
        }

        /* ========== ALERT ========== */
        .alert-custom {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
            font-weight: 500;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .empty-state {
            text-align: center;
            padding: 60px;
            color: #94a3b8;
        }

        /* ========== MODAL KONFIRMASI ========== */
        .confirm-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
        }

        .confirm-modal-overlay.show {
            display: flex;
        }

        .confirm-modal {
            background: #fff;
            border-radius: 16px;
            padding: 30px;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(-20px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .confirm-modal-icon {
            font-size: 48px;
            text-align: center;
            margin-bottom: 15px;
        }

        .confirm-modal-title {
            font-size: 20px;
            font-weight: 700;
            text-align: center;
            color: #1e293b;
            margin-bottom: 10px;
        }

        .confirm-modal-message {
            text-align: center;
            color: #64748b;
            margin-bottom: 25px;
            font-size: 14px;
        }

        .confirm-modal-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .confirm-modal-btn {
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }

        .confirm-modal-btn.cancel {
            background: #e2e8f0;
            color: #475569;
        }

        .confirm-modal-btn.cancel:hover {
            background: #cbd5e1;
        }

        .confirm-modal-btn.danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #fff;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        }

        .confirm-modal-btn.danger:hover {
            transform: translateY(-2px);
        }

        /* ========== RESPONSIVE ========== */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .mobile-menu-btn {
                display: block;
            }
        }

        /* ========== CUSTOM STYLES ========== */
        @yield('styles')
    </style>
</head>

<body>
    {{-- Sidebar --}}
    <aside class="sidebar" id="sidebar">
        <div>
            <div class="sidebar-header">
                <div class="sidebar-logo">🎓</div>
                <div class="sidebar-title">SIAssist Admin</div>
            </div>
            <ul class="sidebar-menu">
                <li>
                    <a href="{{ route('admin.dashboard') }}"
                        class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        📊 Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.users.index') }}"
                        class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        👥 Kelola User
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.upload.index') }}"
                        class="{{ request()->routeIs('admin.upload.*') ? 'active' : '' }}">
                        📁 Dokumen
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.kb.index') }}"
                        class="{{ request()->routeIs('admin.kb.*') ? 'active' : '' }}">
                        🧠 Knowledge Base
                    </a>
                </li>

            </ul>
        </div>
        <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
            @csrf
            <button type="submit" class="logout-btn">🚪 Logout</button>
        </form>
    </aside>

    {{-- Main Content --}}
    <main class="main-content">
        {{-- Header --}}
        <div class="top-header">
            <div>
                <h1 class="header-title">@yield('header', 'Admin')</h1>
                <p style="color:#64748b;">Selamat datang, {{ auth()->user()->name }}!</p>
            </div>
            <div class="user-info">
                <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <div>
                    <h6 style="margin:0;font-size:14px;">{{ auth()->user()->name }}</h6>
                    <p style="margin:0;font-size:12px;color:#64748b;">Administrator</p>
                </div>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="alert-custom">✅ {{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert-custom alert-error">❌ {{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="alert-custom alert-error">❌ {{ $errors->first() }}</div>
        @endif

        {{-- Page Content --}}
        @yield('content')
    </main>

    {{-- Modal Konfirmasi --}}
    <div class="confirm-modal-overlay" id="confirmModal">
        <div class="confirm-modal">
            <div class="confirm-modal-icon">⚠️</div>
            <div class="confirm-modal-title" id="confirmModalTitle">Konfirmasi</div>
            <div class="confirm-modal-message" id="confirmModalMessage">Apakah Anda yakin?</div>
            <div class="confirm-modal-buttons">
                <button class="confirm-modal-btn cancel" onclick="closeConfirmModal()">Batal</button>
                <button class="confirm-modal-btn danger" id="confirmModalAction">Ya, Lanjutkan</button>
            </div>
        </div>
    </div>

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ========== MODAL KONFIRMASI ==========
        let confirmCallback = null;

        function showConfirmModal(title, message, callback) {
            document.getElementById('confirmModalTitle').textContent = title;
            document.getElementById('confirmModalMessage').textContent = message;
            document.getElementById('confirmModal').classList.add('show');
            confirmCallback = callback;
        }

        function closeConfirmModal() {
            document.getElementById('confirmModal').classList.remove('show');
            confirmCallback = null;
        }

        document.getElementById('confirmModalAction').addEventListener('click', function () {
            const callback = confirmCallback;
            closeConfirmModal();
            if (callback) callback();
        });

        // Tutup modal saat klik overlay
        document.getElementById('confirmModal').addEventListener('click', function (e) {
            if (e.target === this) closeConfirmModal();
        });

        // Helper untuk form delete
        function confirmDelete(form, message) {
            showConfirmModal('Hapus Data', message || 'Apakah Anda yakin ingin menghapus?', function () {
                form.submit();
            });
            return false;
        }

        // ========== MOBILE SIDEBAR ==========
        if (window.innerWidth <= 1024) {
            const btn = document.createElement('button');
            btn.innerHTML = '☰';
            btn.style.cssText = 'position:fixed;top:20px;left:20px;z-index:1100;background:#0284c7;color:#fff;border:none;width:50px;height:50px;border-radius:12px;font-size:24px;cursor:pointer';
            btn.onclick = () => document.getElementById('sidebar').classList.toggle('show');
            document.body.appendChild(btn);
        }

        // ========== AUTO-HIDE ALERTS ==========
        setTimeout(() => {
            document.querySelectorAll('.alert-custom').forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>

    @yield('scripts')
</body>

</html>

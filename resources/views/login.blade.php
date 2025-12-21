<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIAssist</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #0ea5e9, #1e3a8a);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-card {
            width: 100%;
            max-width: 430px;
            padding: 40px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.25);
            animation: fadeIn .6s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .icon-large {
            font-size: 60px;
            background: #e0f2fe;
            padding: 20px;
            border-radius: 50%;
            color: #0284c7;
            text-align: center;
            margin: 0 auto 20px auto;
            width: 100px;
            height: 100px;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 4px 12px rgba(14, 165, 233, .3);
        }

        input.form-control {
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 12px;
            font-size: 14px;
            transition: .2s;
        }

        input.form-control:focus {
            background: #fff;
            border-color: #0ea5e9;
            box-shadow: none;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: #0ea5e9;
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 15px;
            margin-top: 10px;
        }

        .btn-login:hover {
            background: #0284c7;
        }

        .alert-custom {
            background: #ef4444;
            color: white;
            padding: 10px 14px;
            border-radius: 10px;
            font-size: 14px;
        }

        .footer-text {
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
            opacity: .7;
        }
    </style>
</head>

<body>

    <div class="login-card">

        <div class="icon-large">💻</div>

        <div class="login-header text-center">
            <h2 class="fw-bold">SIAssist</h2>
            <p class="text-muted">Masuk untuk melanjutkan layanan akademik</p>
        </div>

        {{-- Error Login --}}
        @if(session('error'))
            <div class="alert-custom mb-3">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="alert-custom mb-3">
                @foreach($errors->all() as $err)
                    {{ $err }}<br>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            <div class="mb-3">
                <label>Username</label>
                <input type="text" name="username" class="form-control" value="{{ old('username') }}"
                    placeholder="NIM atau nama.user" required autofocus>
            </div>

            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="********" required>
            </div>

            <button type="submit" class="btn-login">Masuk</button>
        </form>

        <div class="footer-text">© {{ date('Y') }} SIAssist - Sistem Informasi Akademik</div>

    </div>

</body>

</html>

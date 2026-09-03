<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem KTA FSPMI</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #1e3a5f 0%, #2d4a6f 50%, #1e3a5f 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .login-box {
            background: #fff;
            border-radius: 1rem;
            padding: 2.5rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.35);
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1e3a5f;
            margin-bottom: 0.5rem;
        }
        .login-header p {
            color: #64748b;
            font-size: 1rem;
        }
        .login-header .federation {
            color: #b8860b;
            font-weight: 600;
        }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label {
            display: block;
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #334155;
        }
        .form-group input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: 0.2s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #1e3a5f;
            box-shadow: 0 0 0 3px rgba(30,58,95,0.1);
        }
        .btn {
            width: 100%;
            padding: 0.875rem;
            background: #1e3a5f;
            color: #fff;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }
        .btn:hover {
            background: #0f2440;
            transform: translateY(-1px);
        }
        .error {
            background: #fef2f2;
            color: #b91c1c;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.25rem;
            font-size: 0.95rem;
            border-left: 4px solid #b91c1c;
        }
        .remember {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }
        .remember input {
            width: auto;
            width: 1.25rem;
            height: 1.25rem;
            cursor: pointer;
        }
        .remember label {
            margin-bottom: 0;
            font-weight: normal;
            cursor: pointer;
        }
        .forgot {
            text-align: right;
            margin-bottom: 1.5rem;
        }
        .forgot a {
            color: #1e3a5f;
            font-size: 0.95rem;
            text-decoration: none;
            font-weight: 500;
        }
        .forgot a:hover {
            text-decoration: underline;
        }
        .footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e2e8f0;
            color: #94a3b8;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="login-header">
            <h1>KTA FSPMI</h1>
            <p class="federation">Federasi Serikat Pekerja Metal Indonesia</p>
            <p>Sistem Manajemen Kartu Tanda Anggota</p>
        </div>

        @if ($errors->get('email'))
            <div class="error">
                <strong>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                            Login Gagal:
                        </strong> {{ $errors->first('email') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="email@contoh.com">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Masukkan password">
            </div>
            <div class="remember">
                <input type="checkbox" name="remember" id="remember">
                <label for="remember">Ingat saya</label>
            </div>
            <div class="forgot">
                <a href="{{ route('password.request') }}">Lupa password?</a>
            </div>
            <button type="submit" class="btn">Masuk</button>
        </form>

        <div class="footer">
            &copy; {{ date('Y') }} FSPMI. Hak Cipta Dilindungi.
        </div>
    </div>
</body>
</html>

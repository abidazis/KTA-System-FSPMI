<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem KTA Federasi</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-box { background: #fff; border-radius: 1rem; padding: 2.5rem; width: 100%; max-width: 400px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); }
        .login-box h1 { font-size: 1.5rem; font-weight: 700; color: #1e40af; margin-bottom: 0.5rem; text-align: center; }
        .login-box p { color: #64748b; text-align: center; margin-bottom: 2rem; font-size: 0.9rem; }
        .form-group { margin-bottom: 1.25rem; }
        .form-group label { display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem; color: #374151; }
        .form-group input { width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.9rem; transition: 0.2s; }
        .form-group input:focus { outline: none; border-color: #1e40af; box-shadow: 0 0 0 3px rgba(30,64,175,0.1); }
        .btn { width: 100%; padding: 0.75rem; background: #1e40af; color: #fff; border: none; border-radius: 0.5rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: 0.2s; }
        .btn:hover { background: #1e3a8a; }
        .error { background: #fee2e2; color: #991b1b; padding: 0.75rem; border-radius: 0.5rem; margin-bottom: 1rem; font-size: 0.85rem; }
        .remember { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.5rem; }
        .remember input { width: auto; }
        .forgot { text-align: right; margin-bottom: 1.5rem; }
        .forgot a { color: #1e40af; font-size: 0.85rem; text-decoration: none; }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>KTA FSPMI</h1>
        <p>Sistem Manajemen Kartu Tanda Anggota</p>

        @if ($errors->get('email'))
            <div class="error">{{ $errors->first('email') }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="remember">
                <input type="checkbox" name="remember" id="remember">
                <label for="remember" style="margin-bottom:0;font-weight:normal;">Ingat saya</label>
            </div>
            <button type="submit" class="btn">Masuk</button>
        </form>
    </div>
</body>
</html>

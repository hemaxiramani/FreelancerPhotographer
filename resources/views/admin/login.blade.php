<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — Freelancer Photographers</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f6fa; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .login-card { width: 400px; background: #fff; border-radius: 16px; padding: 40px; box-shadow: 0 4px 24px rgba(0,0,0,.08); }
        .brand-logo { display: block; margin: 0 auto 12px; width: 56px; height: 56px; border-radius: 14px; }
        .brand-title { color: #0F6B5E; font-size: 22px; font-weight: 700; text-align: center; margin-bottom: 4px; }
        .brand-sub { color: #6b7280; font-size: 13px; text-align: center; margin-bottom: 32px; }
        .btn-brand { background: #0F6B5E; border-color: #0F6B5E; color: #fff; }
        .btn-brand:hover { background: #0a4f45; border-color: #0a4f45; color: #fff; }
        .form-control:focus { border-color: #0F6B5E; box-shadow: 0 0 0 .2rem rgba(15,107,94,.15); }
    </style>
</head>
<body>
    <div class="login-card">
        <img src="{{ asset('logo-square.png') }}" alt="Logo" class="brand-logo">
        <div class="brand-title">Freelancer Photographers</div>
        <div class="brand-sub">Admin Panel</div>

        @if(session('error'))
            <div class="alert alert-danger py-2" style="font-size:13px;">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.login.post') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label small fw-semibold">Email</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" required autofocus>
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-4">
                <label class="form-label small fw-semibold">Password</label>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <button type="submit" class="btn btn-brand w-100 py-2">Login</button>
        </form>
    </div>
</body>
</html>

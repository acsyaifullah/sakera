<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | SAKERA - Sistem Arsip Kepegawaian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #435ebe;
            --bg-light: #f2f7ff;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-light);
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: white;
            border-radius: 2rem;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            width: 90%;
            max-width: 1000px;
            min-height: 600px;
        }

        /* Split Screen Design */
        .login-row {
            display: flex;
            flex-wrap: wrap;
            min-height: 600px;
        }

        .login-form-section {
            flex: 1;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-image-section {
            flex: 1;
            background: #f8faff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            text-align: center;
        }

        /* Elements Styling */
        .brand-logo h1 {
            font-weight: 800;
            color: var(--primary-color);
            letter-spacing: -1px;
            margin-bottom: 0.5rem;
        }

        .tagline {
            color: #7c8db5;
            font-size: 0.9rem;
            margin-bottom: 2.5rem;
        }

        .form-label {
            font-weight: 600;
            color: #4b4b4b;
            font-size: 0.85rem;
        }

        .form-control {
            padding: 0.8rem 1rem;
            border-radius: 0.7rem;
            border: 1px solid #dce7f1;
            background-color: #f9fbff;
        }

        .form-control:focus {
            box-shadow: 0 0 0 0.25 margin-bottomrem rgba(67, 94, 190, 0.25);
            border-color: var(--primary-color);
        }

        .btn-login {
            background-color: var(--primary-color);
            border: none;
            padding: 0.8rem;
            border-radius: 0.7rem;
            font-weight: 700;
            transition: 0.3s;
            margin-top: 1rem;
        }

        .btn-login:hover {
            background-color: #394ea0;
            transform: translateY(-2px);
        }

        .illustration-img {
            max-width: 70%;
            height: auto;
            margin-bottom: 2rem;
        }

        .promo-text h4 {
            font-weight: 700;
            color: #25396f;
        }

        .promo-text p {
            color: #7c8db5;
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .login-image-section {
                display: none; /* Sembunyikan gambar di HP agar fokus ke form */
            }
            .login-form-section {
                padding: 2rem;
            }
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="login-row">
            
            <div class="login-form-section">
                <div class="brand-logo text-center text-md-start">
                    <h1>SAKERA</h1>
                    <p class="tagline">Sistem Arsip Kepegawaian Responsif & Aman</p>
                </div>

                <h3 class="fw-bold mb-4">Login</h3>

                @if($errors->any())
                    <div class="alert alert-danger py-2 small">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ url('/login') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Username atau NIP</label>
                        <input type="text" name="nip" class="form-control" placeholder="Masukkan NIP Anda" required autofocus>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <label class="form-label">Password</label>
                            <a href="#" class="text-decoration-none small text-primary">Lupa password?</a>
                        </div>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>

                    <div class="mb-4 form-check">
                        <input type="checkbox" class="form-check-input" id="remember">
                        <label class="form-check-label small text-secondary" for="remember">Ingat saya</label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-login w-100 text-white">Login</button>
                </form>

                <div class="mt-5 text-center">
                    <p class="small text-muted">Belum punya akun? <a href="#" class="text-primary fw-bold text-decoration-none">Hubungi Admin</a></p>
                </div>
            </div>

            <div class="login-image-section">
                <img src="{{ asset('images/logo-sakera.png') }}" alt="Ilustrasi Sakera" class="illustration-img">
                <div class="promo-text">
                    <!-- <h4>Pantau Berkas Anda dengan Mudah</h4> -->
                    <p>Kelola seluruh dokumen kepegawaian secara digital dalam satu tempat yang aman dan terorganisir.</p>
                </div>
                
                <div class="d-flex justify-content-center mt-3 gap-2">
                    <span style="height: 4px; width: 30px; background: var(--primary-color); border-radius: 2px;"></span>
                    <span style="height: 4px; width: 30px; background: #dce7f1; border-radius: 2px;"></span>
                    <span style="height: 4px; width: 30px; background: #dce7f1; border-radius: 2px;"></span>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
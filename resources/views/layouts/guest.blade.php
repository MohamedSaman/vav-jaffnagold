<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
         <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
         <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Scripts -->
        <style>
            :root {
                --primary: #161b97;
                --primary-dark: #12167d;
                --black: #000000;
                --white: #ffffff;
            }

            /* Login page styling */
            .login-container {
                height: 100vh;
                width: 100vw;
                position: relative;
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
                background-color: #fcfcfc;
            }

            .background-image {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-image: linear-gradient(rgba(0,0,0,0.2), rgba(0,0,0,0.2)), url('{{ asset('/images/jgg.png') }}');
                background-size: cover;
                background-position: center;
                z-index: 0;
            }

            .login-form-overlay {
                background: linear-gradient(135deg, rgba(255, 255, 255, 0.06), rgba(255, 255, 255, 0.01));
                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
                border-radius: 20px;
                padding: 22px 30px;
                width: 100%;
                max-width: 380px;
                z-index: 1;
                box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
                border: 1px solid rgba(255, 255, 255, 0.15);
            }

            .user-icon-container {
                display: flex;
                justify-content: center;
                margin-bottom: 15px;
            }

            .user-icon-container i {
                font-size: 3rem;
                color: var(--white);
                background: transparent;
                border-radius: 50%;
                width: 60px;
                height: 60px;
                display: flex;
                align-items: center;
                justify-content: center;
                border: none;
                box-shadow: none;
            }

            .form-group {
                margin-bottom: 20px;
            }

            .form-control {
                border-radius: 10px;
                padding: 10px 18px;
                border: 1px solid #ddd;
                transition: all 0.3s;
            }

            .form-control:focus {
                border-color: var(--primary);
                box-shadow: 0 0 0 0.25rem rgba(22, 27, 151, 0.1);
            }

            .login-btn {
                width: 100%;
                border-radius: 12px;
                padding: 12px;
                background: linear-gradient(45deg, #161b97, #2b33c5);
                color: #ffffff;
                border: none;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 2px;
                transition: all 0.3s ease;
                box-shadow: 0 6px 20px rgba(22, 27, 151, 0.3);
            }

            .login-btn:hover {
                background: linear-gradient(45deg, #1d23b3, #3b82f6);
                color: #ffffff;
                transform: translateY(-2px) scale(1.02);
                box-shadow: 0 10px 25px rgba(22, 27, 151, 0.4);
            }

            .forgot-link {
                color: var(--black);
                text-decoration: none;
                font-weight: 500;
                transition: color 0.3s;
            }

            .forgot-link:hover {
                color: var(--primary-dark);
            }
        </style>
        <!-- Styles -->

    </head>
    <body>
        <div class="font-sans text-gray-900 antialiased">
            {{ $slot }}
        </div>

       <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    </body>
</html>

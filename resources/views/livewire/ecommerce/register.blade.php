<div class="login-container register-page">
    <style>
        .register-page {
            min-height: 100vh;
            height: auto;
            width: 100%;
            padding: 32px 16px 24px;
            overflow-y: auto;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }

        .register-page .background-image {
            position: fixed;
            inset: 0;
        }

        .register-shell {
            width: 100%;
            min-height: calc(100vh - 56px);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 1;
            padding: 24px 0;
            flex: 1 0 auto;
        }

        .register-form-overlay {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.06), rgba(255, 255, 255, 0.01));
            padding: 22px 28px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 380px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .register-form-overlay .form-control {
            background: rgba(255, 255, 255, 1) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            border-radius: 8px !important;
            padding: 4px 18px !important;
        }

        .register-form-overlay .form-control::placeholder {
            color: #777;
        }

        .register-title {
            color: #fff;
            text-align: center;
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .register-subtitle {
            color: rgba(255, 255, 255, 0.8);
            text-align: center;
            font-size: 0.9rem;
            margin-bottom: 18px;
        }

        .register-btn {
            background: linear-gradient(45deg, #161b97, #2b33c5) !important;
            border: none !important;
            border-radius: 12px !important;
            padding: 12px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 2px !important;
            color: #fff !important;
            box-shadow: 0 6px 20px rgba(22, 27, 151, 0.3) !important;
            transition: all 0.3s ease !important;
            margin-top: 10px !important;
            width: 100%;
        }

        .register-btn:hover {
            background: linear-gradient(45deg, #1d23b3, #3b82f6) !important;
            box-shadow: 0 10px 25px rgba(22, 27, 151, 0.4) !important;
            transform: translateY(-2px) scale(1.02) !important;
        }

        .separator-line {
            height: 1px;
            background: rgba(255, 255, 255, 0.15);
            margin: 15px 0;
            border-radius: 2px;
        }

        .connect-section {
            text-align: center;
            padding: 5px 0 2px;
        }

        .connect-title {
            color: #fff;
            font-weight: 600;
            font-size: 0.85rem;
            margin-bottom: 10px;
            opacity: 0.8;
        }

        .connect-links {
            display: flex;
            gap: 15px;
            justify-content: center;
            align-items: center;
        }

        .connect-icon {
            width: 38px;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: #fff;
            font-size: 20px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
            text-decoration: none;
            transition: transform 0.2s;
        }

        .connect-icon:hover {
            transform: scale(1.1);
        }

        .connect-icon.email {
            background: #e84b3c;
        }

        .connect-icon.whatsapp {
            background: #25d366;
        }

        .connect-icon i {
            font-size: 22px;
        }

        .form-link {
            color: #fff !important;
            text-decoration: none;
        }

        .form-link:hover {
            color: #fff !important;
            text-decoration: underline;
        }

        .auth-switch {
            text-align: center;
            margin-top: 10px;
        }

        .register-footer {
            position: relative;
            z-index: 1;
            width: 100%;
            text-align: center;
            padding-bottom: 8px;
            flex: 0 0 auto;
        }

        .register-footer p {
            font-size: 0.85rem;
            color: rgba(255,255,255,0.7);
            margin-bottom: 0;
            text-shadow: 0 2px 4px rgba(0,0,0,0.5);
        }

        .register-footer a {
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            opacity: 0.9;
            transition: opacity 0.2s;
        }

        @media (max-width: 420px) {
            .register-page {
                padding: 20px 12px 18px;
            }

            .register-form-overlay {
                padding: 20px;
            }

            .connect-icon {
                width: 52px;
                height: 52px;
            }
        }

        @media (max-height: 760px) {
            .register-shell {
                align-items: flex-start;
            }
        }
    </style>

    <div class="background-image"></div>

    <div class="register-shell">
        <div class="register-form-overlay">
            <div class="user-icon-container">
                <i class="bi bi-person-plus"></i>
            </div>

            <h1 class="register-title">Create Account</h1>
            <p class="register-subtitle">Join Jaffna Gold and start your journey with us</p>

            <form wire:submit.prevent="register">
                <div class="form-group">
                    <input
                        type="text"
                        class="form-control {{ $errors->has('name') ? 'is-invalid shake' : '' }}"
                        wire:model="name"
                        placeholder="Enter Full Name"
                        required
                        aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}">
                    @error('name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <input
                        type="email"
                        class="form-control {{ $errors->has('email') ? 'is-invalid shake' : '' }}"
                        wire:model="email"
                        placeholder="Enter Email Address"
                        required
                        aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}">
                    @error('email')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <input
                        type="tel"
                        class="form-control {{ $errors->has('contact') ? 'is-invalid shake' : '' }}"
                        wire:model="contact"
                        placeholder="Enter Phone Number"
                        required
                        aria-invalid="{{ $errors->has('contact') ? 'true' : 'false' }}">
                    @error('contact')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <input
                        type="password"
                        class="form-control {{ $errors->has('password') ? 'is-invalid shake' : '' }}"
                        wire:model="password"
                        placeholder="Enter Password"
                        required
                        aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}">
                    @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <input
                        type="password"
                        class="form-control"
                        wire:model="password_confirmation"
                        placeholder="Confirm Password"
                        required>
                </div>

                <button type="submit" class="btn btn-primary register-btn">Register</button>

                <div class="auth-switch">
                    <a href="{{ route('login') }}" class="form-link">Already have an account? Sign In</a>
                </div>

                <div class="separator-line"></div>

                <div class="connect-section">
                    <p class="connect-title">Connect with us</p>
                    <div class="connect-links">
                        <a href="mailto:contact@webxkey.com" class="connect-icon email" title="Email us">
                            <i class="bi bi-envelope-fill"></i>
                        </a>
                        <a href="https://api.whatsapp.com/send/?phone=94755299721&text=Hi%21+I%27m+interested+in+your+services.&type=phone_number&app_absent=0"
                            target="_blank"
                            class="connect-icon whatsapp"
                            title="WhatsApp us" rel="noopener noreferrer">
                            <i class="bi bi-whatsapp"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="register-footer">
        <p>
            Developed by <a href="https://webxkey.com/" target="_blank">webxkey</a>
        </p>
    </div>
</div>

    <div class="login-container">
        <style>
            .login-form-overlay { 
                background: linear-gradient(135deg, rgba(255, 255, 255, 0.06), rgba(255, 255, 255, 0.01)); 
                padding: 22px 28px; 
                border-radius: 20px; 
                backdrop-filter: blur(10px); 
                -webkit-backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.15); 
                box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
                width: 100%;
                max-width: 380px;
                margin: 0 20px;
            }
            .login-btn {
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
            }
            .login-btn:hover {
                background: linear-gradient(45deg, #1d23b3, #3b82f6) !important;
                box-shadow: 0 10px 25px rgba(22, 27, 151, 0.4) !important;
                transform: translateY(-2px) scale(1.02) !important;
            }
            .form-control {
                background: rgba(255, 255, 255, 1) !important;
                border: 1px solid rgba(255, 255, 255, 0.2) !important;
                border-radius: 10px !important;
                padding: 5px 18px !important;
            }
            .separator-line { height:1px; background:rgba(255,255,255,0.15); margin:15px 0; border-radius:2px; }
            .connect-section { text-align:center; padding:5px 0 2px; }
            .connect-title { color:#fff; font-weight:600; font-size: 0.85rem; margin-bottom:10px; opacity: 0.8; }
            .connect-links { display:flex; gap:15px; justify-content:center; align-items:center; }
            .connect-icon { width:38px; height:38px; display:inline-flex; align-items:center; justify-content:center; border-radius:50%; color:#fff; font-size:20px; box-shadow:0 8px 24px rgba(0,0,0,0.2); text-decoration:none; transition: transform 0.2s; }
            .connect-icon:hover { transform: scale(1.1); }
            .connect-icon.email { background:#e84b3c; }
            .connect-icon.whatsapp { background:#25d366; }
            .connect-icon i { font-size:22px; }
            .auth-title {
                color: #fff;
                text-align: center;
                font-size: 1.75rem;
                font-weight: 700;
                margin-bottom: 4px;
            }
            .auth-subtitle {
                color: rgba(255, 255, 255, 0.8);
                text-align: center;
                font-size: 0.9rem;
                margin-bottom: 18px;
            }
            /* Make remember label and forgot link white */
            .form-options .form-check-label { color: #fff !important; }
            .forgot-link { color: #fff !important; text-decoration: none; }
            .forgot-link:hover { color: #fff !important; text-decoration: underline; }
            .register-link-wrap { text-align: center; margin-top: 10px; }
            @media (max-width:420px){ .connect-icon{width:52px;height:52px} }
        </style>
        <!-- Full-screen background image -->
        <div class="background-image"></div>

        <!-- Centered login form overlay -->
        <div class="login-form-overlay">
            <!-- User icon -->
            <div class="user-icon-container">
                <i class="bi bi-person-circle"></i>
            </div>

            <h1 class="auth-title">Login</h1>
            <p class="auth-subtitle">Welcome back to Jaffna Gold. Sign in to continue</p>

            <form wire:submit.prevent="login">
          

                <!-- Email/Phone field -->
                <div class="form-group">
                    <input type="text"
                        class="form-control {{ $errors->has('email') ? 'is-invalid shake' : '' }}"
                        wire:model="email"
                        placeholder="Enter Email or Phone Number"
                        required
                        aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}">
                    @error('email')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password field -->
                <div class="form-group">
                    <input type="password"
                        class="form-control {{ $errors->has('password') ? 'is-invalid shake' : '' }}"
                        wire:model="password"
                        placeholder="Enter Password"
                        required
                        aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}">
                    @error('password')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Remember & Forgot options -->
                <div class="d-flex justify-content-between form-options">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" wire:model="remember" id="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    <a href="#" class="forgot-link">Forgot Password</a>
                </div>

                <!-- Login button -->
                <button type="submit" class="btn btn-primary login-btn">Login</button>
                <div class="register-link-wrap">
                    <a href="{{ route('register') }}" class="forgot-link">Don't have an account? Register here</a>
                </div>

                <!-- Separator line -->
                <div class="separator-line"></div>

                <!-- Connect with us section -->
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

        <!-- Developer Credit Outside -->
        <div class="login-footer" style="position: absolute; bottom: 20px; left: 0; width: 100%; text-align: center; z-index: 2;">
            <p style="font-size: 0.85rem; color: rgba(255,255,255,0.7); margin-bottom: 0; text-shadow: 0 2px 4px rgba(0,0,0,0.5);">
                Developed by <a href="https://webxkey.com/" target="_blank" style="color: #fff; text-decoration: none; font-weight: 600; opacity: 0.9; transition: opacity 0.2s;">webxkey</a>
            </p>
        </div>
    </div>

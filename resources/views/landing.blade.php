<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="{{ asset('images/logo_icon.png') }}">
    <title>EduAdapt – Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0066CC 0%, #004D99 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            background: white;
            border-radius: 16px;
            padding: 40px 35px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            text-align: center;
            max-width: 480px;
            width: 90%;
        }

        .logo {
            font-size: 48px;
            margin-bottom: 10px;
        }

        h1 {
            color: #0066CC;
            font-size: 28px;
            margin-bottom: 4px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 25px;
            font-size: 15px;
        }

        .form-group {
            text-align: left;
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin-bottom: 6px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            font-size: 14px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #0066CC;
            box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper input {
            padding-right: 44px;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #0066CC;
            font-size: 20px;
            padding: 5px;
        }

        .form-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px 0 8px;
            font-size: 13px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .remember-me input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #0066CC;
        }

        .forgot-password {
            color: #0066CC;
            font-weight: 600;
            cursor: default;
        }

        /* ---- Forgot password note ---- */
        .forgot-note {
            text-align: left;
            font-size: 12px;
            color: #666;
            margin-bottom: 20px;
            line-height: 1.4;
        }

        /* ---- Login button + loading state ---- */
        .login-btn {
            width: 100%;
            padding: 13px;
            background: #0066CC;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .login-btn:hover:not(:disabled) {
            background: #004D99;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 102, 204, 0.3);
        }

        .login-btn:disabled {
            background: #4d94d6;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
            opacity: 0.9;
        }

        .spinner {
            width: 18px;
            height: 18px;
            border: 2.5px solid rgba(255, 255, 255, 0.4);
            border-top-color: #ffffff;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
            display: none;
            flex-shrink: 0;
        }

        .login-btn.loading .spinner {
            display: block;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .error-message {
            background: #ffe0e0;
            color: #c60000;
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 13px;
            border-left: 4px solid #c60000;
            display: none;
            text-align: left;
        }

        .error-message.show {
            display: block;
        }

        .footer {
            margin-top: 25px;
            color: #999;
            font-size: 13px;
            border-top: 1px solid #f0f0f0;
            padding-top: 20px;
        }

        @media (max-width: 480px) {
            .container {
                padding: 30px 20px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="logo">🎓</div>
        <h1>EduAdapt</h1>
        <p class="subtitle">A Readiness‑Based Multi‑Modal Learning Platform</p>

        @if($errors->any())
        <div class="error-message show">
            {{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}" id="loginForm">
            @csrf

            <!-- Login ID / Username / LRN -->
            <div class="form-group">
                <label for="login_id">Username</label>
                <input type="text" id="login_id" name="login_id" placeholder="Enter your username" required autofocus>
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    <button type="button" class="toggle-password" onclick="togglePassword()">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="3" />
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Form Footer -->
            <div class="form-footer">
                <label class="remember-me">
                    <input type="checkbox" name="remember">
                    <span>Remember me</span>
                </label>
                <!-- <span class="forgot-password">Forgot Password?</span> -->
            </div>

            <!-- Forgot password description -->
            <p class="forgot-note">
                Forgot password? Please go to the Admin Office to request a password change.
            </p>

            <button type="submit" class="login-btn" id="loginBtn">
                <span class="spinner" aria-hidden="true"></span>
                <span class="btn-text">Sign In</span>
            </button>
        </form>

        <div class="footer">
            &copy; {{ date('Y') }} EduAdapt. All rights reserved.
        </div>
    </div>

    <script>
        function togglePassword() {
            const field = document.getElementById('password');
            field.type = field.type === 'password' ? 'text' : 'password';
        }

        const loginForm = document.getElementById('loginForm');
        const loginBtn = document.getElementById('loginBtn');
        const btnText = loginBtn.querySelector('.btn-text');

        loginForm.addEventListener('submit', function () {
            // Prevent double submission
            if (loginBtn.disabled) return;

            loginBtn.disabled = true;
            loginBtn.classList.add('loading');
            btnText.textContent = 'Signing in...';
        });

        // Safety net: if the user returns via browser back/forward cache,
        // reset the button to its normal state.
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                loginBtn.disabled = false;
                loginBtn.classList.remove('loading');
                btnText.textContent = 'Sign In';
            }
        });
    </script>
</body>

</html>
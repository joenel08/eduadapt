<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>EduAdapt – Admin Login</title>
    <style>
        /* Put your full CSS here – same as the sample, but change the title and field label */
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
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 500px;
            overflow: hidden;
        }
        .header {
            background: white;
            padding: 20px 30px;
            border-bottom: 2px solid #0066CC;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo-section {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .logo-icon { font-size: 28px; }
        .logo-text {
            font-size: 20px;
            font-weight: bold;
            color: #0066CC;
        }
        .logo-text .edu { color: #0066CC; }
        .logo-text .adapt { color: #00AA66; }
        .back-link {
            text-decoration: none;
            color: #0066CC;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
        }
        .back-link:hover { color: #004D99; }
        .content { padding: 40px 30px; }
        .login-section h2 {
            font-size: 24px;
            color: #0066CC;
            margin-bottom: 10px;
            font-weight: 700;
            text-align: center;
        }
        .login-section p {
            font-size: 14px;
            color: #666;
            margin-bottom: 30px;
            text-align: center;
        }
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        .password-field-wrapper { position: relative; }
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
        .password-toggle {
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
        .password-toggle svg {
            width: 20px;
            height: 20px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
        }
        .form-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
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
        .remember-me label {
            color: #666;
            cursor: pointer;
            font-weight: 500;
        }
        .forgot-password {
            text-decoration: none;
            color: #0066CC;
            font-weight: 600;
        }
        .forgot-password:hover {
            text-decoration: underline;
        }
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
        }
        .login-btn:hover {
            background: #004D99;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 102, 204, 0.3);
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
        }
        .error-message.show { display: block; }
        .footer {
            text-align: center;
            padding: 20px 30px;
            background: #f5f5f5;
            border-top: 2px solid #0066CC;
            font-size: 13px;
            color: #0066CC;
            font-weight: 500;
        }
        @media (max-width: 600px) {
            .content { padding: 30px 20px; }
            .header { padding: 15px 20px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo-section">
                <div class="logo-icon">🎓</div>
                <div class="logo-text"><span class="edu">Edu</span><span class="adapt">Adapt</span></div>
            </div>
            <a class="back-link" onclick="history.back()">← Back</a>
        </div>

        <div class="content">
            @if($errors->any())
                <div class="error-message show">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="login-section">
                <h2>Admin Login</h2>
                <p>Enter your admin credentials to continue.</p>
            </div>

            <form method="POST" action="{{ route('admin.login.submit') }}">
                @csrf
                <div class="form-group">
                    <label for="login_id">Username</label>
                    <input type="text" id="login_id" name="login_id" placeholder="Enter your username" required autofocus />
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-field-wrapper">
                        <input type="password" id="password" name="password" placeholder="Enter your password" required />
                        <button type="button" class="password-toggle" onclick="togglePassword()" title="Toggle password visibility">
                            <svg id="password-icon" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="3"></circle>
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="form-footer">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember" />
                        <label for="remember">Remember me</label>
                    </div>
                    <a href="#" class="forgot-password">Forgot Password?</a>
                </div>

                <button type="submit" class="login-btn">Login</button>
            </form>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} EduAdapt. All rights reserved.</p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const field = document.getElementById('password');
            const icon = document.getElementById('password-icon');
            if (field.type === 'password') {
                field.type = 'text';
                icon.innerHTML = '<line x1="1" y1="1" x2="23" y2="23"></line><path d="M9.88 9.88A3 3 0 1 0 12.88 12.88M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>';
            } else {
                field.type = 'password';
                icon.innerHTML = '<circle cx="12" cy="12" r="3"></circle><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>';
            }
        }
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="{{ asset('images/logo_icon.png') }}">
    <title>EduAdapt – Choose Your Portal</title>
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
            padding: 50px 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            text-align: center;
            max-width: 600px;
            width: 90%;
        }

        .logo {
            font-size: 48px;
            margin-bottom: 10px;
        }

        h1 {
            color: #0066CC;
            font-size: 32px;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 16px;
        }

        .portal-grid {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .portal-btn {
            display: block;
            padding: 18px;
            border-radius: 10px;
            text-decoration: none;
            color: white;
            font-weight: 700;
            font-size: 18px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .portal-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        .btn-admin {
            background: #1a1a2e;
        }

        .btn-teacher {
            background: #e94560;
        }

        .btn-student {
            background: #0066CC;
        }

        .footer {
            margin-top: 30px;
            color: #999;
            font-size: 14px;
        }

        @media (max-width: 480px) {
            .container {
                padding: 30px 20px;
            }

            h1 {
                font-size: 26px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="logo">🎓</div>
        <h1>EduAdapt</h1>
        <p class="subtitle">A Readiness‑Based Multi‑Modal Learning Platform</p>

        <div class="portal-grid">
            <a href="{{ route('admin.login') }}" class="portal-btn btn-admin">🔐 Admin Portal</a>
            <a href="{{ route('teacher.login') }}" class="portal-btn btn-teacher">👨‍🏫 Teacher Portal</a>
            <a href="{{ route('student.login') }}" class="portal-btn btn-student">🧑‍🎓 Student Portal</a>
        </div>

        <div class="footer">&copy; {{ date('Y') }} EduAdapt. All rights reserved.</div>
    </div>
</body>

</html>
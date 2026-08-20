<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول</title>
    <style>
        body {
            margin: 0;
            font-family: 'Tajawal', sans-serif;
            background: #f0f4ff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* الخلفية بالدوائر الملونة */
        .background-blobs {
            position: absolute;
            top: 0;
            left: 0;
            width: 120%;
            height: 120%;
            overflow: hidden;
            z-index: 0;
            filter: blur(100px);
        }

        .blob {
            position: absolute;
            border-radius: 50%;
            opacity: 0.7;
            animation: move 25s infinite alternate ease-in-out;
        }

        .blob1 {
            width: 500px;
            height: 500px;
            background: #6366f1; /* بنفسجي مزرق */
            top: -100px;
            left: -150px;
            animation-delay: 0s;
        }

        .blob2 {
            width: 400px;
            height: 400px;
            background: #ec4899; /* زهري */
            top: 300px;
            right: -150px;
            animation-delay: 5s;
        }

        .blob3 {
            width: 450px;
            height: 450px;
            background: #fbbf24; /* أصفر */
            bottom: -200px;
            left: 200px;
            animation-delay: 10s;
        }

        @keyframes move {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(80px, -120px) scale(1.2); }
            100% { transform: translate(-100px, 150px) scale(1); }
        }

        /* الصندوق */
        .login-box {
            position: relative;
            z-index: 1;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            padding: 35px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
            text-align: center;
            backdrop-filter: blur(15px);
            animation: fadeIn 1s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }

        /* اللوقو */
        .logo img {
            width: 130px;
            margin-bottom: 15px;
        }

        h1 {
            font-size: 26px;
            color: #0c3c94;
            margin: 10px 0;
        }

        p {
            color: #6b7280;
            margin-bottom: 20px;
            font-size: 15px;
        }

        label {
            display: block;
            text-align: right;
            font-size: 14px;
            margin-bottom: 5px;
            color: #374151;
        }

        .input-group {
            position: relative;
            margin-bottom: 20px;
            text-align: right;
        }

        .input-group input {
            width: 90%;
            padding: 12px 40px 12px 12px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            outline: none;
            font-size: 15px;
            transition: 0.3s;
        }

        .input-group input:focus {
            border-color: #1e90ff;
            box-shadow: 0 0 8px rgba(30, 144, 255, 0.3);
        }

        .input-icon {
            position: absolute;
            top: 50%;
            left: 12px;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 18px;
        }

        .toggle-password {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #9ca3af;
            font-size: 16px;
        }

        .error-message {
            display: block;
            margin-top: 5px;
            font-size: 13px;
            color: #dc2626;
            text-align: right;
            animation: shake 0.3s;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-3px); }
            75% { transform: translateX(3px); }
        }

        .options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .options a {
            color: #0c3c94;
            text-decoration: none;
        }

        .options a:hover {
            text-decoration: underline;
        }

        .login-btn {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            color: white;
            font-weight: bold;
            background: linear-gradient(to right, #0c3c94, #1e90ff);
            cursor: pointer;
            transition: 0.3s ease;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(30, 144, 255, 0.3);
        }

        .register {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }

        .register a {
            color: #1e90ff;
            text-decoration: none;
            font-weight: bold;
        }

        .register a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <!-- الخلفية بالدوائر -->
    <div class="background-blobs">
        <div class="blob blob1"></div>
        <div class="blob blob2"></div>
        <div class="blob blob3"></div>
    </div>

    <!-- الصندوق -->
    <div class="login-box">
        <div class="logo">
            <img src="{{ asset('images/logo.png') }}" alt="شعار شركة الفجر للبترول">
        </div>
        <h1>تسجيل الدخول</h1>
        <p>مرحبا بك في نظام بيان لادراة محطات الوقود</p>

        <form id="loginForm" action="{{ route('login') }}" method="POST">
            @csrf
            <label for="username">اسم المستخدم</label>
            <div class="input-group">
                <input type="text" id="username" name="username" placeholder="أدخل اسم المستخدم" required>
                <span class="input-icon">👤</span>
                @error('username')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <label for="password">كلمة المرور</label>
            <div class="input-group">
                <input type="password" id="password" name="password" placeholder="أدخل كلمة المرور" required>
                <button type="button" class="toggle-password">👁</button>
                <span class="input-icon">🔒</span>
                @error('password')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="options">
                <label><input type="checkbox" name="remember"> تذكرني</label>
            </div>

            <button type="submit" class="login-btn">تسجيل الدخول</button>
        </form>
    </div>

    <script>
        const togglePassword = document.querySelector('.toggle-password');
        const passwordInput = document.getElementById('password');

        togglePassword.addEventListener('click', () => {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                togglePassword.textContent = '🙈';
            } else {
                passwordInput.type = 'password';
                togglePassword.textContent = '👁';
            }
        });
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول | نظام تسجيل بيانات الوقود</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Cairo', sans-serif;
            min-height: 100vh;
        }

        .split {
            display: flex;
            min-height: 100vh;
        }

        .split-form {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.5rem;
            position: relative;
            z-index: 1;
        }

        .login-box {
            width: 100%;
            max-width: 400px;
            background: var(--color-surface);
            border: 1px solid var(--color-border);
            border-radius: 20px;
            padding: 2.5rem 2rem;
            box-shadow: 0 20px 50px rgba(22, 163, 74, 0.12);
            text-align: center;
            animation: fadeIn 0.6s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(14px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo img {
            width: 120px;
            margin-bottom: 12px;
        }

        h1 {
            font-size: 24px;
            font-weight: 700;
            color: var(--color-heading);
            margin: 10px 0 4px;
        }

        .login-sub {
            color: var(--color-text-muted);
            font-size: 14px;
            margin-bottom: 24px;
        }

        label {
            display: block;
            text-align: right;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 6px;
            color: var(--color-text);
        }

        .input-group {
            position: relative;
            margin-bottom: 18px;
            text-align: right;
        }

        .input-group input {
            width: 100%;
            padding: 12px 44px 12px 44px;
            border: 1px solid var(--color-border);
            border-radius: 10px;
            background: var(--color-surface);
            color: var(--color-text);
            font-size: 15px;
            outline: none;
            transition: border-color 0.25s ease, box-shadow 0.25s ease;
        }

        .input-group input::placeholder {
            color: var(--color-text-muted);
            opacity: 0.7;
        }

        .input-group input:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.18);
        }

        .input-icon {
            position: absolute;
            top: 50%;
            inset-inline-start: 14px;
            transform: translateY(-50%);
            color: var(--color-text-muted);
            display: flex;
            align-items: center;
            pointer-events: none;
        }

        .toggle-password {
            position: absolute;
            top: 50%;
            inset-inline-end: 10px;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--color-text-muted);
            display: flex;
            align-items: center;
            padding: 4px;
            border-radius: 6px;
            transition: color 0.2s ease;
        }

        .toggle-password:hover {
            color: var(--color-primary);
        }

        .error-message {
            display: block;
            margin-top: 6px;
            font-size: 13px;
            color: #dc2626;
            text-align: right;
            animation: shake 0.3s;
        }

        html[data-theme="dark"] .error-message {
            color: #f87171;
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
            margin-bottom: 22px;
        }

        .options label {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
            font-weight: 500;
            cursor: pointer;
            color: var(--color-text);
        }

        .options input[type="checkbox"] {
            width: 16px;
            height: 16px;
            cursor: pointer;
            accent-color: var(--color-primary);
        }

        .login-btn {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(135deg, var(--color-primary-strong), var(--color-primary));
            cursor: pointer;
            transition: transform 0.25s ease, box-shadow 0.25s ease, filter 0.25s ease;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 24px rgba(245, 158, 11, 0.35);
            filter: brightness(1.05);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .split-aside {
            flex: 1.05;
            position: relative;
            overflow: hidden;
            color: #fff;
            background: linear-gradient(160deg, #166534 0%, #15803d 48%, #f59e0b 100%);
            display: flex;
            flex-direction: column;
            padding: 3rem 2.5rem;
        }

        html[data-theme="dark"] .split-aside {
            background: linear-gradient(160deg, #052e16 0%, #14532d 48%, #92400e 100%);
        }

        .pattern {
            position: absolute;
            inset: 0;
            background-image:
                repeating-linear-gradient(45deg, rgba(255, 255, 255, 0.07) 0 1px, transparent 1px 26px),
                repeating-linear-gradient(-45deg, rgba(255, 255, 255, 0.07) 0 1px, transparent 1px 26px);
            mask-image: linear-gradient(180deg, rgba(0, 0, 0, 0.9), rgba(0, 0, 0, 0.35));
        }

        .aside-blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(90px);
            opacity: 0.55;
            pointer-events: none;
        }

        .aside-blob-1 {
            width: 420px;
            height: 420px;
            background: rgba(251, 191, 36, 0.5);
            top: -130px;
            inset-inline-start: -130px;
        }

        .aside-blob-2 {
            width: 380px;
            height: 380px;
            background: rgba(74, 222, 128, 0.45);
            bottom: -120px;
            inset-inline-end: -120px;
        }

        .ring {
            position: absolute;
            border: 1.5px solid rgba(255, 255, 255, 0.18);
            border-radius: 50%;
            pointer-events: none;
        }

        .ring-1 {
            width: 320px;
            height: 320px;
            inset-inline-end: -90px;
            top: 16%;
        }

        .ring-2 {
            width: 200px;
            height: 200px;
            inset-inline-end: -24px;
            top: 31%;
        }

        .aside-inner {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
        }

        .aside-top {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .aside-logo {
            width: 52px;
            height: 52px;
            flex-shrink: 0;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 6px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .aside-logo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .aside-brand h2 {
            font-size: 20px;
            font-weight: 700;
        }

        .aside-brand p {
            font-size: 13px;
            opacity: 0.85;
        }

        .aside-hero h3 {
            font-size: 30px;
            font-weight: 700;
            line-height: 1.5;
        }

        .aside-hero > p {
            margin-top: 12px;
            opacity: 0.9;
            line-height: 1.9;
            max-width: 420px;
            font-size: 15px;
        }

        .aside-feats {
            list-style: none;
            margin-top: 28px;
            display: grid;
            gap: 14px;
            max-width: 400px;
        }

        .aside-feats li {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 12px;
            padding: 10px 14px;
            backdrop-filter: blur(4px);
        }

        .aside-feats .tick {
            width: 22px;
            height: 22px;
            flex-shrink: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }

        .aside-foot {
            font-size: 13px;
            opacity: 0.75;
        }

        @media (max-width: 900px) {
            .split {
                flex-direction: column;
            }

            .split-aside {
                display: none;
            }

            .split-form {
                padding: 1.5rem;
            }
        }
    </style>
</head>

<body>
    @include('partials.theme')

    <div class="split">
        <!-- جانب النموذج (يمين في RTL) -->
        <div class="split-form">
            <div class="login-box">
                <div class="logo">
                    <img src="{{ asset('images/logo.png') }}" alt="شعار">
                </div>
                <h1>تسجيل الدخول</h1>
                <p class="login-sub">مرحبا بك في نظام تسجيل بيانات الوقود</p>

                <form id="loginForm" action="{{ route('login') }}" method="POST">
                    @csrf
                    <label for="username">اسم المستخدم</label>
                    <div class="input-group">
                        <input type="text" id="username" name="username" placeholder="أدخل اسم المستخدم" required>
                        <span class="input-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                        </span>
                        @error('username')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <label for="password">كلمة المرور</label>
                    <div class="input-group">
                        <input type="password" id="password" name="password" placeholder="أدخل كلمة المرور" required>
                        <button type="button" class="toggle-password" aria-label="إظهار كلمة المرور" title="إظهار كلمة المرور">
                            <svg id="eyeOpen" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            <svg id="eyeClosed" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94" />
                                <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19" />
                                <path d="M14.12 14.12a3 3 0 1 1-4.24-4.24" />
                                <line x1="1" y1="1" x2="23" y2="23" />
                            </svg>
                        </button>
                        <span class="input-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" />
                                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                            </svg>
                        </span>
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
        </div>

        <!-- الجانب الزخرفي (يسار في RTL) -->
        <div class="split-aside">
            <div class="pattern"></div>
            <div class="aside-blob aside-blob-1"></div>
            <div class="aside-blob aside-blob-2"></div>
            <div class="ring ring-1"></div>
            <div class="ring ring-2"></div>

            <div class="aside-inner">
                <div class="aside-top">
                    <span class="aside-logo">
                        <img src="{{ asset('images/logo.png') }}" alt="شعار">
                    </span>
                    <div class="aside-brand">
                        <h2>نظام تسجيل بيانات الوقود</h2>
                        <p>منظومة إدارة محطات الوقود</p>
                    </div>
                </div>

                <div class="aside-hero">
                    <h3>نظام إدارة محطات الوقود</h3>
                    <p>منصة متكاملة لمتابعة محطات الوقود والمبيعات والمصروفات والأرباح، مع تقارير دقيقة وإدارة شاملة للحسابات والمستخدمين.</p>
                    <ul class="aside-feats">
                        <li>
                            <span class="tick">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                            </span>
                            تسجيل المبيعات والتوريدات بدقة
                        </li>
                        <li>
                            <span class="tick">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                            </span>
                            تقارير الأرباح والمصروفات لحظية
                        </li>
                        <li>
                            <span class="tick">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                            </span>
                            إدارة المحطات والمستخدمين بسهولة
                        </li>
                    </ul>
                </div>

                <p class="aside-foot">&copy; 2026 جميع الحقوق محفوظة</p>
            </div>
        </div>
    </div>

    <script>
        const togglePassword = document.querySelector('.toggle-password');
        const passwordInput = document.getElementById('password');
        const eyeOpen = document.getElementById('eyeOpen');
        const eyeClosed = document.getElementById('eyeClosed');

        togglePassword.addEventListener('click', () => {
            const show = passwordInput.type === 'password';
            passwordInput.type = show ? 'text' : 'password';
            eyeOpen.style.display = show ? 'none' : 'block';
            eyeClosed.style.display = show ? 'block' : 'none';
            togglePassword.setAttribute('aria-label', show ? 'إخفاء كلمة المرور' : 'إظهار كلمة المرور');
            togglePassword.setAttribute('title', show ? 'إخفاء كلمة المرور' : 'إظهار كلمة المرور');
        });
    </script>
</body>
</html>

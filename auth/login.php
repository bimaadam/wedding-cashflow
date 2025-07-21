<?php
session_start();
require_once '../config/koneksi.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($username === '' || $password === '') {
        $error = "Username dan password wajib diisi!";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // PLAIN TEXT COMPARISON — karena lo belum hash
            if ($password === $user['password']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header("Location: ../index.php");
                exit();
            } else {
                $error = "Password salah!";
            }
        } else {
            $error = "Akun tidak ditemukan!";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>GraceFul Wedding - Admin Login</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400;500;600;700&family=Playfair+Display:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #ffeef8 0%, #f8e8f5 25%, #fdf2f8 50%, #f1f0ff 75%, #fef7ed 100%);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        /* Floating hearts animation */
        .heart {
            position: absolute;
            color: rgba(255, 182, 193, 0.3);
            font-size: 20px;
            animation: float 15s infinite linear;
            pointer-events: none;
        }

        .heart:nth-child(1) {
            left: 10%;
            animation-delay: 0s;
        }

        .heart:nth-child(2) {
            left: 20%;
            animation-delay: 2s;
            font-size: 25px;
        }

        .heart:nth-child(3) {
            left: 30%;
            animation-delay: 4s;
        }

        .heart:nth-child(4) {
            left: 40%;
            animation-delay: 6s;
            font-size: 30px;
        }

        .heart:nth-child(5) {
            left: 50%;
            animation-delay: 8s;
        }

        .heart:nth-child(6) {
            left: 60%;
            animation-delay: 10s;
            font-size: 22px;
        }

        .heart:nth-child(7) {
            left: 70%;
            animation-delay: 12s;
        }

        .heart:nth-child(8) {
            left: 80%;
            animation-delay: 14s;
            font-size: 28px;
        }

        @keyframes float {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }

            10% {
                opacity: 1;
            }

            90% {
                opacity: 1;
            }

            100% {
                transform: translateY(-100px) rotate(360deg);
                opacity: 0;
            }
        }

        /* Rose petals */
        .petal {
            position: absolute;
            width: 10px;
            height: 10px;
            background: rgba(255, 182, 193, 0.4);
            border-radius: 50% 0;
            animation: fall 20s infinite linear;
            pointer-events: none;
        }

        .petal:nth-child(odd) {
            background: rgba(255, 105, 180, 0.3);
            animation-duration: 25s;
        }

        @keyframes fall {
            0% {
                transform: translateY(-100px) rotate(0deg);
                opacity: 1;
            }

            100% {
                transform: translateY(100vh) rotate(720deg);
                opacity: 0;
            }
        }

        .container-scroller {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 10;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 30px;
            padding: 60px 50px;
            box-shadow:
                0 25px 50px rgba(0, 0, 0, 0.1),
                0 0 0 1px rgba(255, 255, 255, 0.3);
            border: 2px solid rgba(255, 182, 193, 0.2);
            width: 100%;
            max-width: 450px;
            position: relative;
            animation: slideUp 1s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, #ff69b4, #ffc0cb, #dda0dd, #ff69b4);
            border-radius: 30px;
            z-index: -1;
            animation: borderGlow 3s ease-in-out infinite alternate;
        }

        @keyframes borderGlow {
            0% {
                opacity: 0.5;
            }

            100% {
                opacity: 0.8;
            }
        }

        .brand-logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-icon {
            font-size: 60px;
            background: linear-gradient(45deg, #ff69b4, #dda0dd);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        .welcome-title {
            font-family: 'Dancing Script', cursive;
            font-size: 2.5rem;
            font-weight: 700;
            color: #8b5a8c;
            text-align: center;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .welcome-subtitle {
            font-family: 'Playfair Display', serif;
            font-size: 1.1rem;
            color: #a569a0;
            text-align: center;
            margin-bottom: 40px;
            font-weight: 300;
            letter-spacing: 1px;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #b575b5;
            font-size: 18px;
            z-index: 2;
        }

        .form-control {
            width: 100%;
            padding: 18px 20px 18px 55px;
            border: 2px solid rgba(255, 182, 193, 0.3);
            border-radius: 15px;
            font-size: 16px;
            background: rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }

        .form-control:focus {
            outline: none;
            border-color: #ff69b4;
            box-shadow: 0 0 20px rgba(255, 105, 180, 0.2);
            background: rgba(255, 255, 255, 0.95);
            transform: translateY(-2px);
        }

        .form-control::placeholder {
            color: #b575b5;
            font-weight: 300;
        }

        .btn-login {
            width: 100%;
            padding: 18px;
            border: none;
            border-radius: 15px;
            background: linear-gradient(45deg, #ff69b4, #dda0dd);
            color: white;
            font-size: 18px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(255, 105, 180, 0.4);
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:active {
            transform: translateY(-1px);
        }

        .alert {
            margin-top: 25px;
            padding: 15px 20px;
            border-radius: 12px;
            font-size: 14px;
            animation: shake 0.5s ease-in-out;
        }

        .alert-danger {
            background: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: #dc3545;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-5px);
            }

            75% {
                transform: translateX(5px);
            }
        }

        /* Decorative elements */
        .decoration {
            position: absolute;
            color: rgba(255, 182, 193, 0.2);
            font-size: 120px;
            pointer-events: none;
            z-index: 1;
        }

        .decoration.top-left {
            top: 10%;
            left: 10%;
            animation: rotate 20s linear infinite;
        }

        .decoration.top-right {
            top: 15%;
            right: 15%;
            animation: rotate 25s linear infinite reverse;
        }

        .decoration.bottom-left {
            bottom: 10%;
            left: 5%;
            animation: rotate 30s linear infinite;
        }

        .decoration.bottom-right {
            bottom: 15%;
            right: 10%;
            animation: rotate 22s linear infinite reverse;
        }

        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .login-card {
                margin: 20px;
                padding: 40px 30px;
            }

            .welcome-title {
                font-size: 2rem;
            }

            .decoration {
                font-size: 80px;
            }
        }
    </style>
</head>

<body>
    <!-- Floating hearts -->
    <div class="heart"><i class="fas fa-heart"></i></div>
    <div class="heart"><i class="fas fa-heart"></i></div>
    <div class="heart"><i class="fas fa-heart"></i></div>
    <div class="heart"><i class="fas fa-heart"></i></div>
    <div class="heart"><i class="fas fa-heart"></i></div>
    <div class="heart"><i class="fas fa-heart"></i></div>
    <div class="heart"><i class="fas fa-heart"></i></div>
    <div class="heart"><i class="fas fa-heart"></i></div>

    <!-- Rose petals -->
    <div class="petal" style="left: 15%; animation-delay: 0s;"></div>
    <div class="petal" style="left: 25%; animation-delay: 2s;"></div>
    <div class="petal" style="left: 35%; animation-delay: 4s;"></div>
    <div class="petal" style="left: 45%; animation-delay: 6s;"></div>
    <div class="petal" style="left: 55%; animation-delay: 8s;"></div>
    <div class="petal" style="left: 65%; animation-delay: 10s;"></div>
    <div class="petal" style="left: 75%; animation-delay: 12s;"></div>
    <div class="petal" style="left: 85%; animation-delay: 14s;"></div>

    <!-- Decorative elements -->
    <div class="decoration top-left"><i class="fas fa-ring"></i></div>
    <div class="decoration top-right"><i class="fas fa-dove"></i></div>
    <div class="decoration bottom-left"><i class="fas fa-heart"></i></div>
    <div class="decoration bottom-right"><i class="fas fa-glass-cheers"></i></div>

    <div class="container-scroller">
        <div class="login-card">
            <div class="brand-logo">
                <div class="logo-icon">
                    <i class="fas fa-heart"></i>
                </div>
            </div>

            <h1 class="welcome-title">GraceFul Wedding</h1>
            <p class="welcome-subtitle">Where Dreams Begin</p>
            <p class="welcome-subtitle">
                By <strong><a href="https://engineerhikari.github.io/portfolio" target="_blank">Hikari Takahashi</strong> — 🇯🇵 Designer & Frontend Enthusiast <br>
                Partner in creativity with <strong>Bima Adam</a></strong> — UI/UX Developer
            </p>
            <form method="POST" action="">
                <div class="form-group">
                    <div class="input-wrapper">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" class="form-control" name="username" placeholder="Username" required />
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" class="form-control" name="password" placeholder="Password" required />
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-heart" style="margin-right: 10px;"></i>
                    Login to Paradise
                </button>
            </form>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Add some interactive sparkle effects
        document.addEventListener('DOMContentLoaded', function() {
            // Create random petals
            function createPetal() {
                const petal = document.createElement('div');
                petal.className = 'petal';
                petal.style.left = Math.random() * 100 + '%';
                petal.style.animationDelay = Math.random() * 5 + 's';
                petal.style.animationDuration = (Math.random() * 10 + 15) + 's';
                document.body.appendChild(petal);

                setTimeout(() => {
                    petal.remove();
                }, 25000);
            }

            // Create petals periodically
            setInterval(createPetal, 3000);

            // Add click effect to login button
            const loginBtn = document.querySelector('.btn-login');
            loginBtn.addEventListener('click', function(e) {
                // Create ripple effect
                const ripple = document.createElement('span');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;

                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                ripple.style.position = 'absolute';
                ripple.style.borderRadius = '50%';
                ripple.style.background = 'rgba(255, 255, 255, 0.3)';
                ripple.style.transform = 'scale(0)';
                ripple.style.animation = 'ripple 0.6s linear';
                ripple.style.pointerEvents = 'none';

                this.appendChild(ripple);

                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });

        // CSS for ripple animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(2);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>

</html>
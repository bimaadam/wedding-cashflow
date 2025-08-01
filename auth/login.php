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
    <title>GraceFul Wedding - Login</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:wght@300;400;500;600;700&family=Dancing+Script:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: linear-gradient(rgba(0, 0, 0, 0.4),
                    rgba(0, 0, 0, 0.4)),
                url('https://images.unsplash.com/photo-1519741497674-611481863552?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 8px;
            padding: 48px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .logo {
            text-align: center;
            margin-bottom: 32px;
            position: relative;
        }

        .logo::before {
            content: '';
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 1px;
            background: linear-gradient(90deg, transparent, #d1d5db, transparent);
        }

        .logo::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 1px;
            background: linear-gradient(90deg, transparent, #d1d5db, transparent);
        }

        .logo h1 {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 4px;
            letter-spacing: 1px;
            position: relative;
        }

        .logo h1 .graceful {
            font-family: 'Dancing Script', cursive;
            font-weight: 700;
            color: #8b5a8c;
            font-size: 36px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }

        .logo h1 .wedding {
            font-family: 'Playfair Display', serif;
            font-weight: 300;
            color: #374151;
            font-size: 28px;
            letter-spacing: 3px;
            text-transform: uppercase;
        }

        .logo p {
            font-size: 13px;
            color: #6b7280;
            font-weight: 300;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-top: 8px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 16px;
            background: rgba(255, 255, 255, 0.9);
            transition: all 0.2s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            background: rgba(255, 255, 255, 1);
        }

        .form-input::placeholder {
            color: #9ca3af;
        }

        .login-button {
            width: 100%;
            padding: 12px 24px;
            background: #1a1a1a;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 8px;
        }

        .login-button:hover {
            background: #333;
            transform: translateY(-1px);
        }

        .login-button:active {
            transform: translateY(0);
        }

        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 6px;
            font-size: 14px;
            margin-top: 16px;
        }

        .credits {
            text-align: center;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }

        .credits p {
            font-size: 12px;
            color: #6b7280;
            line-height: 1.5;
        }

        .credits a {
            color: #1a1a1a;
            text-decoration: none;
            font-weight: 500;
        }

        .credits a:hover {
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-container {
                padding: 32px 24px;
                margin: 16px;
            }

            .logo h1 .graceful {
                font-size: 30px;
            }

            .logo h1 .wedding {
                font-size: 24px;
                letter-spacing: 2px;
            }

            .logo::before,
            .logo::after {
                width: 40px;
            }

            .form-input {
                font-size: 16px;
                /* Prevent zoom on iOS */
            }
        }

        /* Loading state */
        .loading {
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        }
    </style>
</head>

<body class="loading">
    <div class="login-container">
        <div class="logo">
            <h1><span class="graceful">GraceFul</span> <span class="wedding">Decoration</span></h1>
            <p>Admin Portal</p>
        </div>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    class="form-input"
                    placeholder="Enter your username"
                    required />
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-input"
                    placeholder="Enter your password"
                    required />
            </div>

            <button type="submit" class="login-button">
                Sign In
            </button>
        </form>

        <?php if ($error): ?>
            <div class="error-message">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="credits">
            <p>
                Designed by <a href="https://engineerhikari.github.io/portfolio" target="_blank">Hikari Takahashi</a><br>
                Developed with <strong>Bima Adam</strong>
            </p>
        </div>
    </div>

    <script>
        // Remove loading class when page is fully loaded
        window.addEventListener('load', function() {
            document.body.classList.remove('loading');
        });

        // Simple form validation feedback
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const inputs = document.querySelectorAll('.form-input');

            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    if (this.value.trim() === '') {
                        this.style.borderColor = '#ef4444';
                    } else {
                        this.style.borderColor = '#d1d5db';
                    }
                });

                input.addEventListener('input', function() {
                    if (this.style.borderColor === 'rgb(239, 68, 68)') {
                        this.style.borderColor = '#d1d5db';
                    }
                });
            });
        });
    </script>
</body>

</html>
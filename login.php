<?php
session_start();
require_once 'includes/logconnect.php';
require_once 'includes/function.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $user = authenticate($username, $password, $conn);
    if ($user) {
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['user_id'] = $user['id']; 

        // Redirect based on role
        if ($user['role'] == 'superuser') {
            header("Location: dashboard.php?role=superuser");
        } elseif ($user['role'] == 'admin') {
            header("Location: admin_dashboard.php?role=admin");
        } else {
            header("Location: user_dashboard.php?role=user");
        }
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form Modern</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="static/css/login.css">

</head>
<body>
    <?php include 'templates/header.php'; ?>
    
    <div class="login-container">
        <div class="login-header">
            <h2>Login</h2>
            <p>Selamat datang kembali!</p>
        </div>
        
        <form method="POST" action="" id="loginForm">
            <div class="error-message" id="errorMessage">
                <?php if (isset($error)) echo $error; ?>
            </div>
            
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="username" id="username" placeholder="Username" required>
            </div>
            
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" id="password" placeholder="Password" required>
            </div>
            
            <button type="submit" class="submit-btn" id="submitBtn">
                Login
            </button>
            
            <div class="register-link">
                <p style="color: white;">Belum punya akun? <a href="register.php" class="underline">Daftar</a></p>

            </div>
        </form>
    </div>

    <?php include 'templates/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            const errorMessage = document.getElementById('errorMessage');
            const submitBtn = document.getElementById('submitBtn');
            
            // Show error message if PHP sets it
            if (errorMessage.textContent.trim() !== '') {
                errorMessage.style.display = 'block';
            }

            // Form validation and animation
            form.addEventListener('submit', function(e) {
                const username = document.getElementById('username').value;
                const password = document.getElementById('password').value;
                
                if (username.trim() === '' || password.trim() === '') {
                    e.preventDefault();
                    errorMessage.textContent = 'Semua field harus diisi!';
                    errorMessage.style.display = 'block';
                    shakeForms();
                }
            });

            // Add loading state to button on submit
            form.addEventListener('submit', function() {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
                submitBtn.disabled = true;
            });

            // Shake animation for invalid form
            function shakeForms() {
                const inputs = document.querySelectorAll('input');
                inputs.forEach(input => {
                    if (input.value.trim() === '') {
                        input.style.animation = 'none';
                        setTimeout(() => {
                            input.style.animation = 'shake 0.5s ease-in-out';
                        }, 10);
                    }
                });
            }

            // Add shake keyframes
            const style = document.createElement('style');
            style.textContent = `
                @keyframes shake {
                    0%, 100% { transform: translateX(0); }
                    25% { transform: translateX(-10px); }
                    75% { transform: translateX(10px); }
                }
            `;
            document.head.appendChild(style);

            // Clear error message when typing
            const inputs = document.querySelectorAll('input');
            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    errorMessage.style.display = 'none';
                });
            });
        });
    </script>
</body>
</html>
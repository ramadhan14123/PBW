<?php
session_start();
require_once 'includes/logconnect.php';
require_once 'includes/function.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    if (register_user($username, $email, $password, $role, $conn)) {
        header("Location: login.php");
        exit;
    } else {
        $error = "Registrasi gagal. Username atau email mungkin sudah terdaftar!";
    }
}
?>

<?php include 'templates/header.php'; ?>
<h2>Register</h2>
<form method="POST" action="">
    <input type="text" name="username" placeholder="Username" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required> 
    <button type="submit">Register</button>
    <?php if (isset($error)) echo "<p>$error</p>"; ?>
</form>
<p>Sudah punya akun? <a href="login.php">Login</a></p>
<?php include 'templates/footer.php'; ?>

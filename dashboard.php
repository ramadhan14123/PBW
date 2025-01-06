<?php
session_start();
require_once 'includes/logconnect.php';
require_once 'includes/function.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['role'] != 'superuser') {
    header("Location: login.php");
    exit;
}

$users = get_all_users($conn);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $new_role = $_POST['role'];

    if (update_user_role($user_id, $new_role, $conn)) {
        $success = "Role berhasil diubah.";
    } else {
        $error = "Gagal mengubah role.";
    }
    $users = get_all_users($conn);
}
?>

<?php include 'templates/header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="static/css/superuserdashboard.css">
</head>
<script>
        function hideAlert(id, timeout) {
            setTimeout(() => {
                const alert = document.getElementById(id);
                if (alert) {
                    alert.style.display = 'none';
                }
            }, timeout);
        }
    </script>
<body>
<div class="container">
    <div class="dashboard-header">
        <h2>Dashboard Superuser</h2>
    </div>

    <?php if (isset($success)): ?>
        <div id="success-message" class="success-message">
            <?php echo htmlspecialchars($success); ?>
        </div>
        <script>
            setTimeout(() => {
                const successMessage = document.getElementById('success-message');
                if (successMessage) {
                    successMessage.style.display = 'none';
                }
            }, 2500); // Menghilang setelah 5 detik
        </script>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div id="error-message" class="error-message">
            <?php echo htmlspecialchars($error); ?>
        </div>
        <script>
            setTimeout(() => {
                const errorMessage = document.getElementById('error-message');
                if (errorMessage) {
                    errorMessage.style.display = 'none';
                }
            }, 5000); // Menghilang setelah 5 detik
        </script>
    <?php endif; ?>

    <h3>Manajemen Role Pengguna</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Akses</th>
        </tr>
        <?php foreach ($users as $user): ?>
        <tr>
            <td><?php echo htmlspecialchars($user['id']); ?></td>
            <td><?php echo htmlspecialchars($user['username']); ?></td>
            <td><?php echo htmlspecialchars($user['email']); ?></td>
            <td><?php echo htmlspecialchars($user['role']); ?></td>
            <td>
                <form method="POST" action="">
                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">
                    <select name="role">
                        <option value="user" <?php if ($user['role'] == 'user') echo 'selected'; ?>>User</option>
                        <option value="admin" <?php if ($user['role'] == 'admin') echo 'selected'; ?>>Admin</option>
                        <option value="superuser" <?php if ($user['role'] == 'superuser') echo 'selected'; ?>>Superuser</option>
                    </select>
                    <button type="submit">Ubah</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <a href="logout.php" class="logout-link">Logout</a>
</div>

<?php include 'templates/footer.php'; ?>
</body>
</html>

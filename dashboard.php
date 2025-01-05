<?php
session_start();
require_once 'includes/logconnect.php';
require_once 'includes/function.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['role'] != 'superuser') {
    header("Location: login.php");
    exit;
}

$users = get_all_users($conn); // Ambil semua data pengguna dari database

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $new_role = $_POST['role'];

    if (update_user_role($user_id, $new_role, $conn)) {
        $success = "Role berhasil diubah.";
    } else {
        $error = "Gagal mengubah role.";
    }

    // Refresh daftar pengguna
    $users = get_all_users($conn);
}
?>

<?php include 'templates/header.php'; ?>
<h2>Dashboard Superuser</h2>

<?php if (isset($success)) echo "<p>$success</p>"; ?>
<?php if (isset($error)) echo "<p>$error</p>"; ?>

<h3>Manajemen Role Pengguna</h3>
<table border="1">
    <tr>
        <th>ID</th>
        <th>Username</th>
        <th>Email</th>
        <th>Role</th>
        <th>Aksi</th>
    </tr>
    <?php foreach ($users as $user): ?>
    <tr>
        <td><?php echo $user['id']; ?></td>
        <td><?php echo $user['username']; ?></td>
        <td><?php echo $user['email']; ?></td>
        <td><?php echo $user['role']; ?></td>
        <td>
            <form method="POST" action="">
                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
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
<a href="logout.php">Logout</a>
<?php include 'templates/footer.php'; ?>

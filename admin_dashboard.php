<?php
session_start();

// Cek apakah pengguna sudah login dan memiliki peran admin
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'user_log');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ambil statistik jumlah pengguna
$total_users = $conn->query("SELECT COUNT(id) AS total FROM users")->fetch_assoc();

// Ambil statistik jumlah postingan
$total_posts = $conn->query("SELECT COUNT(id) AS total FROM posts")->fetch_assoc();

// Ambil data postingan
$result_posts = $conn->query("SELECT * FROM posts");

echo "<h1>Admin Dashboard</h1>";
echo "<a href='logout.php'>Logout</a><br><br>";

// Tampilkan statistik pengguna dan postingan
echo "<h3>Statistics</h3>";
echo "<p>Total Users: " . $total_users['total'] . "</p>";
echo "<p>Total Posts: " . $total_posts['total'] . "</p>";

echo "<hr>";

// **Menampilkan Daftar Postingan**
echo "<h3>Manage Posts</h3>";
echo "<table border='1'>
        <tr>
            <th>Title</th>
            <th>User</th>
            <th>Date Created</th>
            <th>Image</th>
            <th>Actions</th>
        </tr>";

while ($post = $result_posts->fetch_assoc()) {
    // Ambil nama pengguna berdasarkan user_id
    $user_stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $user_stmt->bind_param("i", $post['user_id']);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $user_name = $user_result->fetch_assoc()['username'];

    // Menampilkan gambar jika ada
    $image = !empty($post['image']) ? "<img src='uploads/{$post['image']}' alt='Image' width='100'/>" : "No Image";

    echo "<tr>
            <td>" . htmlspecialchars($post['title']) . "</td>
            <td>" . htmlspecialchars($user_name) . "</td>
            <td>" . $post['created_at'] . "</td>
            <td>" . $image . "</td>
            <td>
                <a href='edit_post.php?id=" . $post['id'] . "'>Edit</a> | 
                <a href='delete_post.php?id=" . $post['id'] . "' onclick=\"return confirm('Are you sure you want to delete this post?');\">Delete</a>
            </td>
          </tr>";
}
echo "</table>";

$conn->close();
?>

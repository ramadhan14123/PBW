<?php
session_start();

// Cek apakah pengguna sudah login dan memiliki peran user
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'user_log');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ambil data user_id dari session
$user_id = $_SESSION['user_id'];

// Siapkan query untuk mengambil postingan berdasarkan user_id yang login
$stmt = $conn->prepare("SELECT * FROM posts WHERE user_id = ?");
$stmt->bind_param("i", $user_id); // Bind user_id sebagai integer
$stmt->execute();
$result_posts = $stmt->get_result(); // Ambil hasil query

echo "<h1>User Dashboard</h1>";
echo "<a href='logout.php'>Logout</a> | <a href='create_post.php'>Buat Postingan Baru</a><br><br>";

echo "<h3>Postingan Anda</h3>";
echo "<table border='1'>
        <tr>
            <th>Judul</th>
            <th>Tanggal Dibuat</th>
            <th>Foto</th>
            <th>Aksi</th>
        </tr>";

if ($result_posts->num_rows > 0) {
    while ($post = $result_posts->fetch_assoc()) {
        echo "<tr>
                <td>" . htmlspecialchars($post['title']) . "</td>
                <td>" . htmlspecialchars($post['created_at']) . "</td>
                <td><img src='" . htmlspecialchars($post['image']) . "' width='100' height='100'></td>
                <td>
                    <a href='edit_post.php?id=" . $post['id'] . "'>Edit</a> | 
                    <a href='delete_post.php?id=" . $post['id'] . "' onclick=\"return confirm('Are you sure you want to delete this post?');\">Hapus</a>
                </td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='4'>Tidak ada postingan ditemukan</td></tr>";
}

echo "</table>";

$stmt->close();
$conn->close();
?>

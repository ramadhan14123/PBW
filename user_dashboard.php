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

// Query untuk mengambil postingan milik user yang sedang login
$stmt_user_posts = $conn->prepare("SELECT * FROM posts WHERE user_id = ?");
$stmt_user_posts->bind_param("i", $user_id);
$stmt_user_posts->execute();
$result_user_posts = $stmt_user_posts->get_result();

// Query untuk mengambil semua postingan dari user lain
$query_all_posts = "SELECT posts.*, users.username 
                    FROM posts 
                    INNER JOIN users ON posts.user_id = users.id";
$result_all_posts = $conn->query($query_all_posts);

echo "<h1>User Dashboard</h1>";
echo "<a href='logout.php'>Logout</a> | <a href='create_post.php'>Buat Postingan Baru</a><br><br>";

// **Bagian 1: Postingan Anda**
echo "<h3>Postingan Anda</h3>";
echo "<table border='1'>
        <tr>
            <th>Judul</th>
            <th>Tanggal Dibuat</th>
            <th>Foto</th>
            <th>Aksi</th>
        </tr>";

if ($result_user_posts->num_rows > 0) {
    while ($post = $result_user_posts->fetch_assoc()) {
        echo "<tr>
                <td>" . htmlspecialchars($post['title']) . "</td>
                <td>" . htmlspecialchars($post['created_at']) . "</td>
                <td><img src='" . htmlspecialchars($post['image']) . "' width='100'></td>
                <td>
                    <a href='edit_post.php?id=" . $post['id'] . "'>Edit</a> | 
                    <a href='delete_post.php?id=" . $post['id'] . "' onclick=\"return confirm('Are you sure you want to delete this post?');\">Hapus</a>
                </td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='4'>Tidak ada postingan Anda ditemukan</td></tr>";
}
echo "</table>";

// **Bagian 2: Postingan Pengguna Lain**
echo "<h3>Postingan Pengguna Lain</h3>";
echo "<table border='1'>
        <tr>
            <th>Judul</th>
            <th>Pengguna</th>
            <th>Tanggal Dibuat</th>
            <th>Foto</th>
        </tr>";

        if ($result_all_posts->num_rows > 0) {
            while ($post = $result_all_posts->fetch_assoc()) {
                // Tampilkan semua postingan, termasuk milik user itu sendiri
                echo "<tr>
                        <td>" . htmlspecialchars($post['title']) . "</td>
                        <td>" . htmlspecialchars($post['username']) . "</td>
                        <td>" . htmlspecialchars($post['created_at']) . "</td>
                        <td><img src='" . htmlspecialchars($post['image']) . "' width='100'></td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='4'>Tidak ada postingan ditemukan</td></tr>";
        }
        echo "</table>";
        

$stmt_user_posts->close();
$conn->close();
?>

<?php
session_start();

// Cek apakah pengguna sudah login
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit();
}

// Cek apakah ada parameter 'id' pada URL
if (isset($_GET['id'])) {
    $post_id = $_GET['id'];

    // Koneksi ke database
    $conn = new mysqli('localhost', 'root', '', 'user_log');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Cek apakah pengguna admin atau user yang memiliki postingan ini
    $post_stmt = $conn->prepare("SELECT user_id FROM posts WHERE id = ?");
    $post_stmt->bind_param("i", $post_id);
    $post_stmt->execute();
    $post_result = $post_stmt->get_result();
    $post = $post_result->fetch_assoc();

    if (!$post) {
        echo "Post not found.";
        exit();
    }

    // Jika admin atau user yang sesuai
    if ($_SESSION['role'] == 'admin' || $_SESSION['user_id'] == $post['user_id']) {
        // Hapus postingan
        $delete_stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
        $delete_stmt->bind_param("i", $post_id);
        if ($delete_stmt->execute()) {
            echo "Post deleted successfully.";
            header("Location: " . ($_SESSION['role'] == 'admin' ? 'admin_dashboard.php' : 'user_dashboard.php'));
        } else {
            echo "Error deleting post: " . $delete_stmt->error;
        }
    } else {
        echo "You do not have permission to delete this post.";
    }

    // Tutup koneksi
    $delete_stmt->close();
    $conn->close();
} else {
    echo "Invalid request.";
}
?>

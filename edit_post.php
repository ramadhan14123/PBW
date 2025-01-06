<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $post_id = $_GET['id'];

    $conn = new mysqli('localhost', 'root', '', 'user_log');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Ambil data postingan berdasarkan id
    $stmt = $conn->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $post_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $post = $result->fetch_assoc();
    } else {
        echo "Post tidak ditemukan.";
        exit();
    }

    // Handle form submission untuk update
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $title = $_POST['title'];
        $content = $_POST['content'];

        $update_stmt = $conn->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ?");
        $update_stmt->bind_param("ssi", $title, $content, $post_id);
        if ($update_stmt->execute()) {
            header("Location: user_dashboard.php"); // Redirect setelah update
            exit();
        } else {
            echo "Gagal memperbarui postingan.";
        }
    }
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Postingan</title>
</head>
<body>
    <h1>Edit Postingan</h1>
    <form method="POST" action="">
        <label for="title">Judul:</label>
        <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($post['title']); ?>" required><br><br>

        <label for="content">Konten:</label><br>
        <textarea name="content" id="content" rows="5" required><?php echo htmlspecialchars($post['content']); ?></textarea><br><br>

        <button type="submit">Update Post</button>
    </form>
</body>
</html>

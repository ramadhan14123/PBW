<?php
session_start();

// Cek apakah pengguna sudah login dan memiliki peran user
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit();
}

// Periksa apakah ID postingan tersedia
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "ID postingan tidak valid.";
    exit();
}

$post_id = intval($_GET['id']);

$conn = new mysqli('localhost', 'root', '', 'user_log');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ambil data postingan berdasarkan ID dan user_id
$stmt = $conn->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $post_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

// Periksa apakah postingan ditemukan
if ($result->num_rows === 0) {
    echo "Postingan tidak ditemukan atau Anda tidak memiliki akses.";
    exit();
}

$post = $result->fetch_assoc();
$current_image = $post['image']; // Simpan path gambar saat ini

// Proses pembaruan jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $new_image = $current_image; // Default ke gambar saat ini

    // Validasi input
    if (empty($title)) {
        echo "Judul tidak boleh kosong.";
    } else {
        // Periksa apakah ada gambar baru yang diunggah
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/';
            $image_name = basename($_FILES['image']['name']);
            $target_path = $upload_dir . time() . '_' . $image_name;

            // Validasi ekstensi file
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            $file_extension = strtolower(pathinfo($target_path, PATHINFO_EXTENSION));

            if (in_array($file_extension, $allowed_extensions)) {
                // Pindahkan file ke folder tujuan
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                    $new_image = $target_path;

                    // Hapus gambar lama jika ada
                    if ($current_image && file_exists($current_image)) {
                        unlink($current_image);
                    }
                } else {
                    echo "Gagal mengunggah gambar.";
                }
            } else {
                echo "Format file gambar tidak valid. Gunakan jpg, jpeg, png, atau gif.";
            }
        }

        // Update postingan di database
        $update_stmt = $conn->prepare("UPDATE posts SET title = ?, image = ? WHERE id = ?");
        $update_stmt->bind_param("ssi", $title, $new_image, $post_id);

        if ($update_stmt->execute()) {
            header("Location: user_posts.php"); // Redirect setelah berhasil update
            exit();
        } else {
            echo "Gagal memperbarui postingan.";
        }
    }
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Postingan</title>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="static/css/edit_posts.css">
</head>
<body>
    <div class="sidebar">
        <div class="menu-btn">
            <i class="ph-bold ph-caret-left"></i>
        </div>
        <div class="head">
            <div class="user-details">
                <p class="title"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                <p class="name"><?php echo htmlspecialchars($_SESSION['role']); ?></p>
            </div>
        </div>
        <div class="nav">
            <div class="menu">
                <p class="title">Main</p>
                <ul>
                    <li>
                        <a href="user_posts.php">
                            <i class="ph-bold ph-house-simple"></i>
                            <span class="text">Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="create_post.php">
                            <i class="ph-bold ph-plus-circle"></i>
                            <span class="text">Create Post</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="menu">
                <p class="title">Settings</p>
                <ul>
                    <li>
                        <a href="logout.php">
                            <i class="ph-bold ph-sign-out"></i>
                            <span class="text">Logout</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="form-container">
            <h2>Edit Postingan</h2>
            <form method="POST" action="" enctype="multipart/form-data">
                <label for="title">Judul:</label>
                <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($post['title']); ?>" required><br><br>

                <label for="image">Gambar:</label><br>
                <?php if ($current_image): ?>
                    <img src="<?php echo htmlspecialchars($current_image); ?>" width="150" alt="Gambar saat ini"><br>
                <?php endif; ?>
                <input type="file" name="image" id="image" accept=".jpg, .jpeg, .png, .gif"><br><br>

                <button type="submit">Update Post</button>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.js"></script>
    <script>
        $(document).ready(function() {
            $('.menu-btn').click(function() {
                $('.sidebar').toggleClass('active');
            });
        });
    </script>
</body>
</html>

<?php
session_start();

// Cek apakah pengguna sudah login dan memiliki peran user
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $title = $_POST['title'];
    $image = $_FILES['image'];

    // Tentukan folder tempat menyimpan gambar
    $uploadDir = 'uploads/';
    $uploadFile = $uploadDir . basename($image['name']);

    // Pastikan file yang diupload adalah gambar
    if (getimagesize($image['tmp_name']) !== false) {
        // Proses gambar (upload)
        if (move_uploaded_file($image['tmp_name'], $uploadFile)) {
            // Koneksi ke database
            $conn = new mysqli('localhost', 'root', '', 'user_log');
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Siapkan query untuk menyimpan postingan baru
            $stmt = $conn->prepare("INSERT INTO posts (user_id, title, image, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("iss", $_SESSION['user_id'], $title, $uploadFile);
            if ($stmt->execute()) {
                // Setelah berhasil menyimpan, arahkan ke user_dashboard.php
                header("Location: user_posts.php");
                exit();
            } else {
                echo "Terjadi kesalahan saat menyimpan postingan.";
            }

            $stmt->close();
            $conn->close();
        } else {
            echo "Gambar gagal diupload.";
        }
    } else {
        echo "File yang diupload bukan gambar.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Postingan</title>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="static/css/create_post.css">
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
                    <li class="active">
                        <a href="create_post.php">
                            <i class="ph-bold ph-plus-circle"></i>
                            <span class="text">Create Post</span>
                        </a>
                    </li>
                    <li>
                        <a href="other_posts.php">
                            <i class="ph-bold ph-globe"></i>
                            <span class="text">Explore</span>
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
            <h2>Buat Postingan</h2>
            <form method="POST" enctype="multipart/form-data">
                <label for="title">Judul Postingan</label>
                <input type="text" name="title" id="title" required><br><br>

                <label for="image">Gambar Postingan</label>
                <input type="file" name="image" id="image" accept="image/*" required><br><br>

                <button type="submit">Buat Postingan</button>
            </form>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.js"></script>
    <script>
        $(document).ready(function() {
            $('.menu-btn').click(function() {
                $('.sidebar').toggleClass('active');
            });
            // Toggle submenu
            $('.menu ul li a').click(function(e) {
                if($(this).find('.arrow').length > 0) {
                    e.preventDefault();
                    $(this).parent().toggleClass('active');
                }
            });
        });
    </script>
</body>
    </script>
</body>
</html>

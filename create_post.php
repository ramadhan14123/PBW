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
    <style>
        :root {
            --dark-bg: #1a1a1a;
            --darker-bg: #121212;
            --card-bg: #242424;
            --gold: #FFD700;
            --gold-hover: #FFC800;
            --text-primary: #ffffff;
            --text-secondary: #b3b3b3;
            --sidebar-width: 260px;
            --sidebar-collapsed-width: 75px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--dark-bg);
            color: var(--text-primary);
            line-height: 1.6;
            display: flex;
        }

        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--darker-bg);
            padding: 20px;
            position: fixed;
            left: 0;
            transition: all 0.3s ease;
            z-index: 1000;
            border-right: 1px solid var(--gold);
            overflow-y: auto;
        }

        .sidebar.active {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar.active .user-details,
        .sidebar.active .menu .title,
        .sidebar.active .text {
            display: none;
        }

        .menu-btn {
            position: absolute;
            right: -12px;
            top: 20px;
            background: var(--gold);
            color: var(--darker-bg);
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 1001;
        }

        .head {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
        }

        .user-img {
            min-width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid var(--gold);
        }

        .user-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-details {
            white-space: nowrap;
            overflow: hidden;
        }

        .user-details .title {
            color: var(--gold);
            font-weight: 600;
        }

        .user-details .name {
            color: var(--text-secondary);
            font-size: 0.9em;
        }

        .nav .menu {
            margin-bottom: 30px;
        }

        .menu .title {
            color: var(--text-secondary);
            font-size: 0.8em;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .menu ul {
            list-style: none;
        }

        .menu ul li {
            margin-bottom: 5px;
        }

        .menu ul li a {
            color: var(--text-primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 10px;
            border-radius: 8px;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .menu ul li a:hover, 
        .menu ul li.active a {
            background: var(--gold);
            color: var(--darker-bg);
        }

        .menu ul li a i {
            margin-right: 10px;
            font-size: 1.2em;
            min-width: 20px;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            width: calc(100% - var(--sidebar-width));
            transition: all 0.3s ease;
        }

        .sidebar.active + .main-content {
            margin-left: var(--sidebar-collapsed-width);
            width: calc(100% - var(--sidebar-collapsed-width));
        }

        .form-container {
            background: var(--card-bg);
            padding: 30px;
            border-radius: 12px;
            width: 100%;
            max-width: 600px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        h2 {
            color: var(--gold);
            text-align: center;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-size: 1.1em;
            color: var(--text-primary);
        }

        input[type="text"], input[type="file"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 2px solid var(--gold);
            border-radius: 8px;
            background-color: #333;
            color: var(--text-primary);
            font-size: 1em;
        }

        button[type="submit"] {
            background-color: var(--gold);
            color: var(--darker-bg);
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            font-size: 1.1em;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s ease;
        }

        button[type="submit"]:hover {
            background-color: var(--gold-hover);
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="menu-btn">
            <i class="ph-bold ph-caret-left"></i>
        </div>
        <div class="head">
            <div class="user-img">
                <img src="user.jpg" alt="User" />
            </div>
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
        });
    </script>
</body>
</html>

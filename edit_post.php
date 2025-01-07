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

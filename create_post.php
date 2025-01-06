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
                header("Location: user_dashboard.php");
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

<form method="POST" enctype="multipart/form-data">
    <label for="title">Judul Postingan</label>
    <input type="text" name="title" id="title" required><br><br>

    <label for="image">Gambar Postingan</label>
    <input type="file" name="image" id="image" accept="image/*" required><br><br>

    <button type="submit">Buat Postingan</button>
</form>

<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'user_log');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$total_users = $conn->query("SELECT COUNT(id) AS total FROM users")->fetch_assoc();
$total_posts = $conn->query("SELECT COUNT(id) AS total FROM posts")->fetch_assoc();
$result_posts = $conn->query("SELECT * FROM posts");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Admin Dashboard</h1>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <div class="stats-container">
            <div class="stat-card">
                <i class="fas fa-users"></i>
                <div class="stat-number"><?php echo $total_users['total']; ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-file-alt"></i>
                <div class="stat-number"><?php echo $total_posts['total']; ?></div>
                <div class="stat-label">Total Posts</div>
            </div>
        </div>

        <h2 class="section-title">Manage Posts</h2>
        <div class="posts-container">
            <?php while ($post = $result_posts->fetch_assoc()): 
                $user_stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
                $user_stmt->bind_param("i", $post['user_id']);
                $user_stmt->execute();
                $user_result = $user_stmt->get_result();
                $user_name = $user_result->fetch_assoc()['username'];
            ?>
                <div class="post-card">
                    <div class="post-header">
                        <div class="post-title"><?php echo htmlspecialchars($post['title']); ?></div>
                        <div class="post-meta">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($user_name); ?><br>
                            <i class="fas fa-calendar"></i> <?php echo date('d M Y', strtotime($post['created_at'])); ?>
                        </div>
                    </div>
                    <?php if (!empty($post['image'])): ?>
                    <div class="post-image-container">
                        <img src="<?php echo $post['image']; ?>" alt="Post image" class="post-image">
                    </div>
                    <?php endif; ?>
                    <div class="post-actions">
                        <a href="delete_post.php?id=<?php echo $post['id']; ?>" onclick="return confirm('Are you sure you want to delete this post?');" class="delete-btn">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>

<?php $conn->close(); ?>
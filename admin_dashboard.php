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
    <style>
        :root {
            --dark-bg: #1a1a1a;
            --darker-bg: #121212;
            --card-bg: #242424;
            --gold: #FFD700;
            --gold-hover: #FFC800;
            --text-primary: #ffffff;
            --text-secondary: #b3b3b3;
            --danger: #ff4444;
            --danger-hover: #cc0000;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--dark-bg);
            color: var(--text-primary);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header Styles */
        .header {
            background: var(--darker-bg);
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            margin-bottom: 30px;
            border-radius: 10px;
            border: 1px solid var(--gold);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            color: var(--gold);
            font-size: 24px;
            font-weight: 600;
        }

        .logout-btn {
            background-color: var(--danger);
            color: var(--text-primary);
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background-color: var(--danger-hover);
            transform: translateY(-2px);
        }

        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--card-bg);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            text-align: center;
            border: 1px solid var(--gold);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card i {
            font-size: 24px;
            color: var(--gold);
            margin-bottom: 10px;
        }

        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: var(--text-primary);
        }

        .stat-label {
            color: var(--text-secondary);
            font-size: 14px;
        }

        /* Posts Grid */
        .posts-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .post-card {
            background: var(--card-bg);
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            overflow: hidden;
            transition: transform 0.3s ease;
            border: 1px solid var(--gold);
        }

        .post-card:hover {
            transform: translateY(-5px);
        }

        .post-header {
            padding: 15px;
            border-bottom: 1px solid var(--gold);
        }

        .post-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--gold);
            margin-bottom: 5px;
        }

        .post-meta {
            color: var(--text-secondary);
            font-size: 14px;
        }

        .post-meta i {
            color: var(--gold);
            margin-right: 5px;
        }

        .post-image-container {
            width: 100%;
            height: 200px;
            overflow: hidden;
            border-bottom: 1px solid var(--gold);
        }

        .post-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .post-actions {
            padding: 15px;
            display: flex;
            justify-content: flex-end;
        }

        .delete-btn {
            background-color: var(--danger);
            color: var(--text-primary);
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .delete-btn:hover {
            background-color: var(--danger-hover);
            transform: translateY(-2px);
        }

        .section-title {
            font-size: 20px;
            color: var(--gold);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--gold);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }

            .stats-container {
                grid-template-columns: 1fr;
            }

            .posts-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
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
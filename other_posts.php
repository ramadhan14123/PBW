<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'user_log');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query_all_posts = "SELECT posts.*, users.username 
                    FROM posts 
                    INNER JOIN users ON posts.user_id = users.id 
                    ORDER BY posts.created_at DESC";
$result_all_posts = $conn->query($query_all_posts);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Posts</title>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="static/css/other_posts.css">
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
                    <li class="active">
                        <a href="#">
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
        <div class="masonry-grid">
            <?php if ($result_all_posts->num_rows > 0): 
                while ($post = $result_all_posts->fetch_assoc()): ?>
                    <div class="pin">
                        <?php if (!empty($post['image'])): ?>
                            <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="Post Image" class="pin-image">
                        <?php endif; ?>
                        <div class="pin-content">
                            <div class="pin-title"><?php echo htmlspecialchars($post['title']); ?></div>
                            <div class="pin-author">
                                <i class="ph-bold ph-user"></i> 
                                <?php echo htmlspecialchars($post['username']); ?>
                            </div>
                            <div class="pin-date">
                                <i class="ph-bold ph-clock"></i> 
                                <?php echo date('d M Y', strtotime($post['created_at'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile;
            else: ?>
                <div class="no-posts">
                    <i class="ph-bold ph-image" style="font-size: 48px; color: var(--gold); margin-bottom: 20px;"></i>
                    <h2>No Posts Yet</h2>
                    <p>There are no posts to display at the moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.js"></script>
    <script>
        $(document).ready(function() {
            $('.menu-btn').click(function() {
                $('.sidebar').toggleClass('active');
            });
            $('.menu ul li a').click(function(e) {
                if($(this).find('.arrow').length > 0) {
                    e.preventDefault();
                    $(this).parent().toggleClass('active');
                }
            });
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>
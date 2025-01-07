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

$user_id = $_SESSION['user_id'];
$stmt_user_posts = $conn->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
$stmt_user_posts->bind_param("i", $user_id);
$stmt_user_posts->execute();
$result_user_posts = $stmt_user_posts->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Posts</title>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="static/css/user_posts.css">
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
                    <li class="active">
                        <a href="#">
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
        <div class="masonry-grid">
            <?php if ($result_user_posts->num_rows > 0): 
                while ($post = $result_user_posts->fetch_assoc()): ?>
                    <div class="pin">
                        <?php if (!empty($post['image'])): ?>
                            <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="Post Image" class="pin-image">
                        <?php endif; ?>
                        <div class="pin-content">
                            <div class="pin-title"><?php echo htmlspecialchars($post['title']); ?></div>
                            <div class="pin-date">
                                <i class="ph-bold ph-clock"></i> 
                                <?php echo date('d M Y', strtotime($post['created_at'])); ?>
                            </div>
                            <div class="pin-actions">
                                <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="pin-button edit-btn">
                                    <i class="ph-bold ph-pencil"></i> Edit
                                </a>
                                <a href="delete_post.php?id=<?php echo $post['id']; ?>" 
                                   onclick="return confirm('Are you sure you want to delete this post?');" 
                                   class="pin-button delete-btn">
                                    <i class="ph-bold ph-trash"></i> Delete
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile;
            else: ?>

            <?php endif; ?>
        </div>
        <div class="nopost-container">
            <div class="no-posts">
             <i class="ph-bold ph-image" style="font-size: 48px; color: var(--gold); margin-bottom: 20px;"></i>
                 <h2>No Posts Yet</h2>
                 <p>Start creating your first post!</p>
                 <p><a href="create_post.php" style="color: #FFD700; text-decoration: none; font-weight: bold;" onmouseover="this.style.color='#FFD700'; this.style.textDecoration='underline';" onmouseout="this.style.color='#FFD700'; this.style.textDecoration='none';">Posting Sekarang</a></p>
             </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.js"></script>
    <script>
        $(document).ready(function() {
            // Toggle sidebar
            $('.menu-btn').click(function() {
                $('.sidebar').toggleClass('active');
            });
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
        
            // Show or hide no-post message
            if ($('.masonry-grid').children().length === 0) {
                $('.nopost-container').show();
            } else {
                $('.nopost-container').hide();
            }
        });
        });
    </script>
</body>
</html>

<?php
$stmt_user_posts->close();
$conn->close();
?>
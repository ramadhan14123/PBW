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
        }

        .sidebar.active {
            width: var(--sidebar-collapsed-width);
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
        }

        .head {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
        }

        .user-img {
            width: 40px;
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
        }

        .menu ul li a:hover, 
        .menu ul li.active a {
            background: var(--gold);
            color: var(--darker-bg);
        }

        .menu ul li a i {
            margin-right: 10px;
            font-size: 1.2em;
        }

        /* Main Content */
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

        /* Masonry Grid */
        .masonry-grid {
            columns: 5 200px;
            column-gap: 20px;
        }

        .pin {
            break-inside: avoid;
            background: var(--card-bg);
            border-radius: 16px;
            margin-bottom: 20px;
            overflow: hidden;
            transition: transform 0.3s ease;
            border: 1px solid var(--gold);
        }

        .pin:hover {
            transform: translateY(-5px);
        }

        .pin-image {
            width: 100%;
            display: block;
        }

        .pin-content {
            padding: 15px;
        }

        .pin-title {
            color: var(--gold);
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .pin-author {
            color: var(--text-primary);
            font-size: 14px;
            margin-bottom: 5px;
        }

        .pin-date {
            color: var(--text-secondary);
            font-size: 12px;
            margin-bottom: 10px;
        }

        .no-posts {
            text-align: center;
            padding: 40px;
            background: var(--card-bg);
            border-radius: 16px;
            margin: 20px auto;
            max-width: 400px;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: var(--sidebar-collapsed-width);
            }
            
            .main-content {
                margin-left: var(--sidebar-collapsed-width);
                width: calc(100% - var(--sidebar-collapsed-width));
            }

            .masonry-grid {
                columns: 2 160px;
            }
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
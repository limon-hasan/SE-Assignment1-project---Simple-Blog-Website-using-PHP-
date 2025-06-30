<?php
session_start();
require_once 'includes/config.php';

// Insert default categories if the table is empty
$stmt = $pdo->query("SELECT COUNT(*) FROM categories");
if ($stmt->fetchColumn() == 0) {
    $pdo->query("INSERT INTO categories (name) VALUES ('Technology'), ('Lifestyle'), ('Education')");
}

// Handle add category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category']) && !empty(trim($_POST['new_category']))) {
    $new_cat = trim($_POST['new_category']);
    $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
    $stmt->execute([$new_cat]);
    header("Location: index.php");
    exit();
}

// Handle delete category
if (isset($_GET['delete_category'])) {
    $cat_id = intval($_GET['delete_category']);
    // Delete all posts in this category (and their comments)
    $stmt = $pdo->prepare("SELECT id FROM posts WHERE category_id = ?");
    $stmt->execute([$cat_id]);
    $post_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if ($post_ids) {
        // Delete comments for these posts
        $in = str_repeat('?,', count($post_ids) - 1) . '?';
        $pdo->prepare("DELETE FROM comments WHERE post_id IN ($in)")->execute($post_ids);
        // Delete posts
        $pdo->prepare("DELETE FROM posts WHERE id IN ($in)")->execute($post_ids);
    }
    // Now delete the category
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$cat_id]);
    header("Location: index.php");
    exit();
}

// Fetch categories (move this after add/delete logic)
$stmt = $pdo->query("SELECT * FROM categories");
$categories = $stmt->fetchAll();

// Get selected category if any
$category_id = isset($_GET['category']) ? $_GET['category'] : null;

// Prepare the query based on category filter
if ($category_id) {
    $query = "SELECT p.*, u.username, c.name as category_name 
              FROM posts p 
              JOIN users u ON p.user_id = u.id 
              JOIN categories c ON p.category_id = c.id 
              WHERE p.category_id = ?
              ORDER BY p.created_at DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$category_id]);
} else {
    $query = "SELECT p.*, u.username, c.name as category_name 
              FROM posts p 
              JOIN users u ON p.user_id = u.id 
              JOIN categories c ON p.category_id = c.id 
              ORDER BY p.created_at DESC";
    $stmt = $pdo->query($query);
}

$posts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BlogPost - Home</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { background: #fff; }
        .categories-section {
            background: #f6f6f6;
            padding: 32px 0 18px 0;
        }
        .categories-title {
            font-size: 1.1em;
            font-weight: 600;
            margin-bottom: 18px;
            color: #222;
        }
        .categories-list {
            display: flex;
            gap: 18px;
            flex-wrap: wrap;
        }
        .category-btn {
            background: #3498db;
            color: #fff;
            border: none;
            border-radius: 22px;
            padding: 7px 20px;
            font-size: 0.97em;
            font-weight: 500;
            margin-bottom: 8px;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        .category-btn:hover, .category-btn.active {
            background: #2ecc71;
            color: #fff;
        }
        .posts-container {
            max-width: 900px;
            margin: 32px auto 0 auto;
            padding: 0 18px;
        }
        .post-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.07);
            padding: 24px 18px 18px 18px;
            margin-bottom: 28px;
            transition: box-shadow 0.2s;
            position: relative;
        }
        .post-title {
            font-size: 1.3em;
            font-weight: 700;
            color: #222;
            margin-bottom: 8px;
        }
        .post-image {
            display: block;
            margin: 14px 0 14px 0;
            border-radius: 10px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.09);
            max-width: 100%;
            max-height: 220px;
        }
        .post-preview {
            font-size: 0.98em;
            color: #444;
            margin-bottom: 12px;
            line-height: 1.5;
        }
        .read-more {
            color: #3498db;
            text-decoration: underline;
            font-weight: 600;
            font-size: 0.98em;
            margin-bottom: 8px;
            display: inline-block;
            transition: color 0.2s;
        }
        .read-more:hover {
            color: #217dbb;
        }
        .post-meta {
            color: #888;
            font-size: 0.93em;
            margin-bottom: 8px;
        }
        .post-actions {
            margin-top: 8px;
        }
        .post-actions button, .post-actions a {
            background: #333;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 6px 14px;
            font-size: 0.97em;
            font-weight: 500;
            margin-right: 6px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s;
            display: inline-block;
        }
        .post-actions button:hover, .post-actions a:hover {
            background: #222;
        }
        @media (max-width: 700px) {
            .posts-container { padding: 0 2vw; }
            .post-card { padding: 12px 4px 10px 4px; }
            .post-title { font-size: 1em; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="create_post.php">Create Post</a></li>
                <li><a href="includes/logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="categories-section">
        <div class="posts-container">
            <div class="categories-title">Categories:</div>
            <div class="categories-list">
                <a href="index.php" class="category-btn<?php if (!isset($_GET['category'])) echo ' active'; ?>">All</a>
                <?php foreach ($categories as $cat): ?>
                    <span style="display:inline-flex;align-items:center;">
                        <a href="index.php?category=<?php echo $cat['id']; ?>" class="category-btn<?php if (isset($_GET['category']) && $_GET['category'] == $cat['id']) echo ' active'; ?>">
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </a>
                        <a href="index.php?delete_category=<?php echo $cat['id']; ?>" onclick="return confirm('Delete this category?');" style="color:#e74c3c;font-size:1.2em;margin-left:4px;text-decoration:none;font-weight:bold;">&times;</a>
                    </span>
                <?php endforeach; ?>
                <!-- Add Category Button -->
                <form action="" method="POST" style="display:inline-block; margin-left:18px;">
                    <input type="text" name="new_category" placeholder="New category" style="padding:6px; border-radius:16px; border:1px solid #ccc; font-size:0.97em;">
                    <button type="submit" name="add_category" style="background:#2ecc71; color:#fff; border:none; border-radius:16px; padding:7px 18px; font-size:0.97em; font-weight:500; cursor:pointer;">Add</button>
                </form>
            </div>
        </div>
    </div>

    <div class="posts-container">
        <?php foreach ($posts as $post): ?>
            <div class="post-card">
                <div class="post-title"><?php echo htmlspecialchars($post['title']); ?></div>
                <?php if (!empty($post['image'])): ?>
                    <img src="uploads/<?php echo htmlspecialchars($post['image']); ?>" alt="Post Image" class="post-image">
                <?php endif; ?>
                <div class="post-preview">
                    <?php
                        $preview = mb_substr(strip_tags($post['content']), 0, 200);
                        echo htmlspecialchars($preview);
                        if (mb_strlen(strip_tags($post['content'])) > 200) {
                            echo "...";
                        }
                    ?>
                </div>
                <a href="post_detail.php?id=<?php echo $post['id']; ?>" class="read-more">Read More</a>
                <div class="post-meta">
                    By <?php echo htmlspecialchars($post['username']); ?> | Category: <?php echo htmlspecialchars($post['category_name']); ?> | 
                    <?php
                        $datetime = $post['updated_at'] ?? $post['created_at'];
                        echo date('F j, Y, g:i A', strtotime($datetime));
                    ?>
                </div>
                <div class="post-actions">
                    <a href="edit_post.php?id=<?php echo $post['id']; ?>">Edit</a>
                    <a href="delete_post.php?id=<?php echo $post['id']; ?>" onclick="return confirm('Are you sure you want to delete this post?');">Delete</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div style="height: 120px;"></div>
    <footer style="text-align:center; margin: 80px 0 30px 0; color: #888; font-size: 1em;">
        Developed by Maksudul Hasan Limon
    </footer>
</body>
</html>

<?php
session_start();
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$post_id = intval($_GET['id']);

// Fetch post details
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch();

if (!$post) {
    echo "<div style='text-align:center;margin-top:60px;color:#888;font-size:1.2em;'>Post not found.</div>";
    exit();
}

// Fetch categories
$stmt = $pdo->query("SELECT * FROM categories");
$categories = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post - BlogPost</title>
    <link rel="stylesheet" href="css/style.css">
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

    <div class="container">
        <h2>Edit Post</h2>
        <form action="includes/edit_post_process.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($post['title']); ?>" required>
            </div>
            <div class="form-group">
                <label for="category_id">Category:</label>
                <select name="category_id" class="form-control" required>
                    <?php foreach($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" <?php if($category['id'] == $post['category_id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="content">Content:</label>
                <textarea name="content" class="form-control" rows="6" required><?php echo htmlspecialchars($post['content']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="image">Image:</label>
                <?php if (!empty($post['image'])): ?>
                    <div>
                        <img src="uploads/<?php echo htmlspecialchars($post['image']); ?>" alt="Current Image" style="max-width:200px;"><br>
                        <small>Current image. Upload a new one to replace.</small>
                    </div>
                <?php endif; ?>
                <input type="file" name="image" accept="image/*" class="form-control">
            </div>
            <button type="submit" class="btn">Update Post</button>
        </form>
    </div>
    <footer style="text-align:center; margin: 80px 0 30px 0; color: #888; font-size: 1em;">
        Developed by Maksudul Hasan Limon
    </footer>
</body>
</html>

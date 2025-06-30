<?php
session_start();
require_once 'includes/config.php';

// Insert default categories if the table is empty
$stmt = $pdo->query("SELECT COUNT(*) FROM categories");
if ($stmt->fetchColumn() == 0) {
    $pdo->query("INSERT INTO categories (name) VALUES ('Technology'), ('Lifestyle'), ('Education')");
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
    <title>Create Post - BlogPost</title>
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
        <h2>Create New Post</h2>
        <form action="includes/create_post_process.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="category_id">Category:</label>
                <select name="category_id" class="form-control" required>
                    <?php foreach($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="content">Content:</label>
                <textarea name="content" class="form-control" rows="6" required></textarea>
            </div>
            <div class="form-group">
                <label for="image">Image:</label>
                <input type="file" name="image" accept="image/*" class="form-control">
            </div>
            <button type="submit" class="btn">Create Post</button>
        </form>
    </div>
</body>
</html>

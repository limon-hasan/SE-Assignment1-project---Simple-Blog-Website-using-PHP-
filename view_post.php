<?php
session_start();
require_once 'includes/config.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$post_id = $_GET['id'];

// Fetch post details
$stmt = $pdo->prepare("SELECT p.*, u.username, c.name as category_name 
                       FROM posts p 
                       JOIN users u ON p.user_id = u.id 
                       JOIN categories c ON p.category_id = c.id 
                       WHERE p.id = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch();

if (!$post) {
    header('Location: index.php');
    exit();
}

// Fetch comments
$stmt = $pdo->prepare("SELECT c.*, u.username 
                       FROM comments c 
                       JOIN users u ON c.user_id = u.id 
                       WHERE c.post_id = ? 
                       ORDER BY c.created_at DESC");
$stmt->execute([$post_id]);
$comments = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> - BlogPost</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <ul>
                <li><a href="index.php">Home</a></li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="create_post.php">Create Post</a></li>
                    <li><a href="includes/logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="post-detail">
            <?php if (!empty($post['image'])): ?>
                <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="Post Image" class="post-image" style="max-width:100%;height:auto;" />
            <?php endif; ?>
            <h2><?php echo htmlspecialchars($post['title']); ?></h2>
            <div class="post-meta">
                By <?php echo htmlspecialchars($post['username']); ?> |
                Category: <?php echo htmlspecialchars($post['category_name']); ?> |
                <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
            </div>
            <div class="post-content">
                <?php echo nl2br(htmlspecialchars($post['content'])); ?>
            </div>
            
            <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id']): ?>
                <div class="post-actions">
                    <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="btn">Edit</a>
                    <a href="delete_post.php?id=<?php echo $post['id']; ?>" 
                       class="btn" onclick="return confirm('Are you sure?')">Delete</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="comments-section">
            <h3>Comments</h3>
            <?php if(isset($_SESSION['user_id'])): ?>
                <form action="includes/add_comment.php" method="POST">
                    <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                    <div class="form-group">
                        <textarea name="content" class="form-control" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn">Add Comment</button>
                </form>
            <?php endif; ?>

            <div class="comments">
                <?php foreach($comments as $comment): ?>
                    <div class="comment">
                        <div class="comment-meta">
                            By <?php echo htmlspecialchars($comment['username']); ?> | 
                            <?php echo date('F j, Y', strtotime($comment['created_at'])); ?>
                        </div>
                        <div class="comment-content">
                            <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>

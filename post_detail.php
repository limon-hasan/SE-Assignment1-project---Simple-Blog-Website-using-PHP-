<?php
session_start();
require_once 'includes/config.php';

// Fetch the post by ID from the URL
$post_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $pdo->prepare("SELECT p.*, u.username AS author, c.name AS category_name FROM posts p JOIN users u ON p.user_id = u.id JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch();

if (!$post) {
    echo "<div style='text-align:center;margin-top:60px;color:#888;font-size:1.2em;'>Post not found.</div>";
    exit;
}

// Fetch comments with user name and email
$stmt = $pdo->prepare("
    SELECT c.content, u.username AS name, u.email, c.created_at
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.post_id = ?
    ORDER BY c.created_at ASC
");
$stmt->execute([$post_id]);
$comments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($post['title']); ?> - BlogPost</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .comments-section {
            margin-top: 40px;
            max-width: 700px;
        }
        .comments-title {
            font-size: 1.2em;
            color: #3498db;
            font-weight: 600;
            margin-bottom: 18px;
        }
        .comment-card {
            background: #f8fbff;
            border: 1px solid #e3eaf1;
            border-radius: 10px;
            margin-bottom: 18px;
            padding: 16px 18px 12px 18px;
            box-shadow: 0 2px 8px rgba(52,152,219,0.04);
        }
        .comment-header {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 4px;
        }
        .comment-email {
            font-size: 0.97em;
            color: #888;
            font-weight: normal;
            margin-left: 8px;
        }
        .comment-content {
            margin: 8px 0 6px 0;
            color: #444;
            font-size: 1.03em;
        }
        .comment-date {
            font-size: 0.92em;
            color: #b0b0b0;
            text-align: right;
        }
        .no-comments {
            color: #aaa;
            font-style: italic;
            margin-bottom: 18px;
        }
        .comment-form {
            margin-top: 36px;
            background: #f4faff;
            border-radius: 10px;
            padding: 18px 16px 14px 16px;
            box-shadow: 0 2px 8px rgba(52,152,219,0.04);
            max-width: 700px;
        }
        .comment-form textarea {
            width: 100%;
            min-height: 70px;
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #b5d0ea;
            font-size: 1em;
            margin-bottom: 10px;
            resize: vertical;
        }
        .comment-form input[type="submit"] {
            padding: 8px 22px;
            border: none;
            border-radius: 5px;
            background: linear-gradient(90deg, #3498db 60%, #6dd5fa 100%);
            color: #fff;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .comment-form input[type="submit"]:hover {
            background: linear-gradient(90deg, #217dbb 60%, #3498db 100%);
        }
    </style>
</head>
<body>
    <div class="container">
        <div style="color:#3498db; font-size:1.1em; font-weight:600; margin-bottom:12px;">
            <?php echo htmlspecialchars($post['category_name']); ?>
        </div>
        <h2><?php echo htmlspecialchars($post['title']); ?></h2>
        <div style="color:#888; font-size:1.05em; margin-bottom:18px;">
            By <strong><?php echo htmlspecialchars($post['author']); ?></strong>
            | Category: <?php echo htmlspecialchars($post['category_name']); ?>
            | <?php
                $datetime = $post['updated_at'] ?? $post['created_at'];
                echo date('F j, Y, g:i A', strtotime($datetime));
            ?>
        </div>
        <!-- Go Back to Home Button -->
        <div style="margin-bottom: 24px;">
            <a href="index.php" style="display:inline-block; background:#3498db; color:#fff; padding:8px 22px; border-radius:6px; text-decoration:none; font-weight:600; font-size:1em; transition:background 0.2s;">&larr; Back to Home</a>
        </div>
        <?php if (!empty($post['image'])): ?>
            <a href="uploads/<?php echo htmlspecialchars($post['image']); ?>" target="_blank">
                <img src="uploads/<?php echo htmlspecialchars($post['image']); ?>" alt="Post Image" style="max-width:400px;">
            </a>
        <?php endif; ?>
        <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>

        <div class="comments-section">
            <div class="comments-title">Comments</div>
            <?php if ($comments): ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="comment-card">
                        <div class="comment-header">
                            <?php echo htmlspecialchars($comment['name']); ?>
                            <span class="comment-email">(<?php echo htmlspecialchars($comment['email']); ?>)</span>
                        </div>
                        <div class="comment-content">
                            <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                        </div>
                        <div class="comment-date">
                            <?php echo htmlspecialchars($comment['created_at']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-comments">No comments yet.</div>
            <?php endif; ?>
        </div>

        <?php if (isset($_SESSION['user_id'])): ?>
            <form action="post_comment.php" method="POST" class="comment-form">
                <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                <textarea name="content" required placeholder="Write your comment here..."></textarea><br>
                <input type="submit" value="Submit Comment">
            </form>
        <?php else: ?>
            <p><a href="login.php">Log in</a> to leave a comment.</p>
        <?php endif; ?>
    </div>
    <footer style="text-align:center; margin: 80px 0 30px 0; color: #888; font-size: 1em;">
        Developed by Maksudul Hasan Limon
    </footer>
</body>
</html>

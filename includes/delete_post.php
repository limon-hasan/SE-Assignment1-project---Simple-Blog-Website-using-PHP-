<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header('Location: ../index.php');
    exit();
}

$post_id = $_GET['id'];

// Verify post ownership
$stmt = $pdo->prepare("SELECT user_id FROM posts WHERE id = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch();

if ($post['user_id'] != $_SESSION['user_id']) {
    header('Location: ../index.php');
    exit();
}

// Delete comments first due to foreign key constraint
$stmt = $pdo->prepare("DELETE FROM comments WHERE post_id = ?");
$stmt->execute([$post_id]);

// Delete post
$stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");

try {
    $stmt->execute([$post_id]);
    $_SESSION['success'] = "Post deleted successfully!";
} catch(PDOException $e) {
    $_SESSION['error'] = "Failed to delete post!";
}

header('Location: ../index.php');
exit();
?>

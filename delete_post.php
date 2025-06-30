<?php
session_start();
require_once 'includes/config.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$post_id = $_GET['id'];

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

header('Location: index.php');
exit();

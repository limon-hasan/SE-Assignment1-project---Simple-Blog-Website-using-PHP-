<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post_id = $_POST['post_id'];
    $user_id = $_SESSION['user_id'];
    $content = $_POST['content'];

    $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
    
    try {
        $stmt->execute([$post_id, $user_id, $content]);
        $_SESSION['success'] = "Comment added successfully!";
    } catch(PDOException $e) {
        $_SESSION['error'] = "Failed to add comment!";
    }

    header('Location: ../view_post.php?id=' . $post_id);
    exit();
}
?>

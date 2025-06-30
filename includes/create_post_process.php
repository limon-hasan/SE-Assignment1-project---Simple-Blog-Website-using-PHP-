<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $title = $_POST['title'];
    $category_id = $_POST['category_id'];
    $content = $_POST['content'];

    // Handle image upload
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $uploads_dir = '../uploads';
        if (!is_dir($uploads_dir)) {
            mkdir($uploads_dir, 0777, true);
        }
        $tmp_name = $_FILES['image']['tmp_name'];
        $basename = basename($_FILES['image']['name']);
        $target_file = $uploads_dir . '/' . uniqid() . '_' . $basename;
        if (move_uploaded_file($tmp_name, $target_file)) {
            $image_path = substr($target_file, 3); // remove '../' for web path
        }
    }

    $stmt = $pdo->prepare("INSERT INTO posts (user_id, category_id, title, content, image) VALUES (?, ?, ?, ?, ?)");
    try {
        $stmt->execute([$user_id, $category_id, $title, $content, $image_path]);
        $_SESSION['success'] = "Post created successfully!";
        header('Location: ../index.php');
        exit();
    } catch(PDOException $e) {
        $_SESSION['error'] = "Failed to create post!";
        header('Location: ../create_post.php');
        exit();
    }
}
?>

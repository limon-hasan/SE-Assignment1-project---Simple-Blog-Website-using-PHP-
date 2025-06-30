<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post_id = $_POST['post_id'];
    $title = $_POST['title'];
    $category_id = $_POST['category_id'];
    $content = $_POST['content'];

    // Fetch current image filename
    $stmt = $pdo->prepare("SELECT image FROM posts WHERE id = ?");
    $stmt->execute([$post_id]);
    $current = $stmt->fetch();
    $image_filename = $current ? $current['image'] : null;

    // Handle new image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = mime_content_type($_FILES['image']['tmp_name']);
        if (in_array($file_type, $allowed_types)) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $new_image_filename = uniqid('img_', true) . '.' . $ext;
            $upload_dir = '../uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $new_image_filename);

            // Optionally delete the old image file
            if ($image_filename && file_exists($upload_dir . $image_filename)) {
                unlink($upload_dir . $image_filename);
            }

            $image_filename = $new_image_filename;
        }
    }

    // Update post in database
    $stmt = $pdo->prepare("UPDATE posts SET title=?, category_id=?, content=?, image=?, updated_at=NOW() WHERE id=?");
    $stmt->execute([
        $title,
        $category_id,
        $content,
        $image_filename,
        $post_id
    ]);

    header("Location: ../post_detail.php?id=" . $post_id);
    exit;
}
?>

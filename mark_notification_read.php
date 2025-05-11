<?php
require_once 'includes/header.php';

// Hanya user yang login yang dapat mengakses
if (!isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notification_id'])) {
    $notification_id = $_POST['notification_id'];
    $user_id = $_SESSION['user_id'];
    
    // Tandai notifikasi sebagai dibaca
    executeQuery("UPDATE notifications SET is_read = 1 
                 WHERE notification_id = ? AND user_id = ?", 
                [$notification_id, $user_id]);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?><?php
require_once 'includes/header.php';

// Hanya user yang login yang dapat mengakses
if (!isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notification_id'])) {
    $notification_id = $_POST['notification_id'];
    $user_id = $_SESSION['user_id'];
    
    // Tandai notifikasi sebagai dibaca
    executeQuery("UPDATE notifications SET is_read = 1 
                 WHERE notification_id = ? AND user_id = ?", 
                [$notification_id, $user_id]);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
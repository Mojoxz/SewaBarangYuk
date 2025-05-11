<?php
// Cek apakah file dipanggil secara langsung
$script_path = str_replace('\\', '/', dirname($_SERVER['SCRIPT_FILENAME']));
$root_path = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$relative_path = str_replace($root_path, '', $script_path);

// Tentukan base path untuk include file
$is_root = ($script_path == $root_path . '/rental_system' || $script_path == $root_path . '/rental_system/');
$base_path = $is_root ? '' : '../';

require_once $base_path . 'includes/header.php';

// Hanya user yang login yang dapat mengakses
if (!isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$last_id = $_GET['last_id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Ambil notifikasi baru
$notifications = fetchAll("SELECT * FROM notifications 
                          WHERE user_id = ? AND notification_id > ? 
                          ORDER BY created_at ASC", 
                         [$user_id, $last_id]);

// Hitung jumlah notifikasi yang belum dibaca
$unread_count = fetchOne("SELECT COUNT(*) as count FROM notifications 
                         WHERE user_id = ? AND is_read = 0", 
                        [$user_id])['count'];

// Return hasil dalam format JSON
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'notifications' => $notifications,
    'unread_count' => $unread_count
]);
?>
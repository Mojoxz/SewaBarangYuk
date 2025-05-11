<?php
// Cek apakah file dipanggil secara langsung
$script_path = str_replace('\\', '/', dirname($_SERVER['SCRIPT_FILENAME']));
$root_path = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$relative_path = str_replace($root_path, '', $script_path);

// Tentukan base path untuk include file
$is_root = ($script_path == $root_path . '/rental_system' || $script_path == $root_path . '/rental_system/');
$base_path = $is_root ? '' : '../';

require_once $base_path . 'includes/header.php';

// Verifikasi apakah user sudah login
if (!isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Fungsi untuk menjalankan tugas-tugas yang sama dengan cronjob.php
function checkDeadlines() {
    // Array untuk menyimpan notifikasi baru
    $new_notifications = [];
    $user_id = $_SESSION['user_id'];
    
    // Cek pengembalian yang terlambat
    checkLateReturns();
    
    // Kirim notifikasi pengingat untuk user yang sedang login
    $reminders = fetchAll("SELECT r.*, i.name as item_name, i.owner_id, u.name as renter_name, u.user_id as renter_id
                          FROM rentals r 
                          JOIN items i ON r.item_id = i.item_id 
                          JOIN users u ON r.renter_id = u.user_id
                          WHERE r.status IN ('confirmed', 'active') 
                          AND DATEDIFF(r.end_date, CURDATE()) IN (3, 1, 0)
                          AND r.return_status = 0
                          AND (r.renter_id = ? OR i.owner_id = ?)
                          AND NOT EXISTS (
                              SELECT 1 FROM notifications n 
                              WHERE n.related_id = r.rental_id 
                              AND n.notification_type = 'reminder'
                              AND DATE(n.created_at) = CURDATE()
                              AND n.user_id = ?
                          )", [$user_id, $user_id, $user_id]);

    foreach ($reminders as $reminder) {
        $days_left = (strtotime($reminder['end_date']) - strtotime('today')) / (60 * 60 * 24);
        
        if ($days_left == 3 || $days_left == 1 || $days_left == 0) {
            $day_text = $days_left == 0 ? 'hari ini' : "{$days_left} hari lagi";
            
            // Cek apakah user saat ini adalah penyewa atau pemilik
            if ($reminder['renter_id'] == $user_id) {
                // Buat notifikasi untuk penyewa
                $notification_title = "Pengingat Pengembalian";
                $notification_message = "Pengembalian {$reminder['item_name']} jatuh tempo {$day_text}. Harap segera kembalikan barang untuk menghindari denda keterlambatan.";
                
                $notification_id = createNotification(
                    $user_id,
                    $notification_title,
                    $notification_message,
                    "reminder",
                    $reminder['rental_id']
                );
                
                // Tambahkan ke array notifikasi baru
                $new_notifications[] = [
                    'notification_id' => $notification_id,
                    'title' => $notification_title,
                    'message' => $notification_message,
                    'created_at' => date('Y-m-d H:i:s')
                ];
            } 
            else if ($reminder['owner_id'] == $user_id) {
                // Buat notifikasi untuk pemilik
                $notification_title = "Pengingat Pengembalian";
                $notification_message = "Pengembalian {$reminder['item_name']} oleh {$reminder['renter_name']} jatuh tempo {$day_text}.";
                
                $notification_id = createNotification(
                    $user_id,
                    $notification_title,
                    $notification_message,
                    "reminder",
                    $reminder['rental_id']
                );
                
                // Tambahkan ke array notifikasi baru
                $new_notifications[] = [
                    'notification_id' => $notification_id,
                    'title' => $notification_title,
                    'message' => $notification_message,
                    'created_at' => date('Y-m-d H:i:s')
                ];
            }
        }
    }
    
    // Hitung jumlah notifikasi yang belum dibaca
    $unread_count = fetchOne("SELECT COUNT(*) as count FROM notifications 
                            WHERE user_id = ? AND is_read = 0", 
                            [$user_id])['count'];
    
    return [
        'success' => true,
        'notifications' => $new_notifications,
        'unread_count' => $unread_count
    ];
}

// Jalankan tugas cron dan kembalikan hasil
header('Content-Type: application/json');
echo json_encode(checkDeadlines());
?>
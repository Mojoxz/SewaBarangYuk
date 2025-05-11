<?php
// Fungsi untuk mengalihkan halaman
// Fungsi untuk mengalihkan halaman dengan lebih robust
function redirect($url) {
    // Jika URL sudah absolut (dimulai dengan http:// atau https://)
    if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) {
        header("Location: $url");
        exit();
    }
    
    // Jika URL dimulai dengan / (absolute path dari root domain)
    if (substr($url, 0, 1) === '/') {
        header("Location: $url");
        exit();
    }
    
    // Tentukan base path untuk relative URL
    $base_path = '';
    $current_path = $_SERVER['PHP_SELF'];
    
    // Jika file berada di subfolder (owner atau renter)
    if (strpos($current_path, '/owner/') !== false || strpos($current_path, '/renter/') !== false) {
        $base_path = '../';
    }
    
    // Periksa jika URL sudah mengandung base_path
    if (!empty($base_path) && strpos($url, $base_path) === 0) {
        header("Location: $url");
    } else {
        header("Location: $base_path$url");
    }
    exit();
}

// Fungsi untuk memeriksa apakah user sudah login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Fungsi untuk mendapatkan informasi user yang login
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $sql = "SELECT * FROM users WHERE user_id = ?";
    return fetchOne($sql, [$_SESSION['user_id']]);
}

// Fungsi untuk memeriksa apakah user adalah pemilik
function isOwner() {
    $user = getCurrentUser();
    return $user && ($user['user_type'] == 'owner' || $user['user_type'] == 'both');
}

// Fungsi untuk memeriksa apakah user adalah penyewa
function isRenter() {
    $user = getCurrentUser();
    return $user && ($user['user_type'] == 'renter' || $user['user_type'] == 'both');
}

// Fungsi untuk upload file
function uploadFile($file, $directory) {
    $target_dir = "assets/images/uploads/$directory/";
    
    // Pastikan direktori ada
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $filename = time() . '_' . basename($file["name"]);
    $target_file = $target_dir . $filename;
    
    // Check jika file adalah gambar
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
        return [
            'success' => false,
            'message' => 'Hanya file JPG, JPEG, dan PNG yang diperbolehkan.'
        ];
    }
    
    // Upload file
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return [
            'success' => true,
            'filename' => $filename
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Terjadi kesalahan saat mengupload file.'
        ];
    }
}

// Fungsi untuk menghitung harga total sewa
function calculateTotalPrice($price_per_day, $start_date, $end_date) {
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $interval = $start->diff($end);
    $days = $interval->days + 1; // Termasuk hari terakhir
    
    return $price_per_day * $days;
}

// Fungsi untuk membuat notifikasi
// Fungsi untuk membuat notifikasi
function createNotification($user_id, $title, $message, $type, $related_id = null) {
    $sql = "INSERT INTO notifications (user_id, title, message, notification_type, related_id) 
            VALUES (?, ?, ?, ?, ?)";
    executeQuery($sql, [$user_id, $title, $message, $type, $related_id]);
    
    // Jika notifikasi dibuat, fungsi akan mengembalikan ID notifikasi baru
    return lastInsertId();
}

// Fungsi untuk mendapatkan jumlah notifikasi yang belum dibaca
function getUnreadNotificationsCount($user_id) {
    $sql = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0";
    $result = fetchOne($sql, [$user_id]);
    return $result['count'];
}

// Fungsi untuk cek apakah ada pengembalian yang terlambat dan membuat late fee
function checkLateReturns() {
    $sql = "SELECT * FROM rentals WHERE status = 'active' AND end_date < CURDATE() AND return_status = 0";
    $late_rentals = fetchAll($sql);
    
    foreach ($late_rentals as $rental) {
        // Update status menjadi terlambat
        executeQuery("UPDATE rentals SET status = 'late' WHERE rental_id = ?", [$rental['rental_id']]);
        
        // Hitung jumlah hari terlambat
        $end_date = new DateTime($rental['end_date']);
        $today = new DateTime();
        $days_late = $today->diff($end_date)->days;
        
        // Hitung denda (10% dari total harga per hari)
        $late_fee_amount = $rental['total_price'] * 0.1 * $days_late;
        
        // Buat entri denda
        $sql = "INSERT INTO late_fees (rental_id, amount, days_late) VALUES (?, ?, ?)";
        executeQuery($sql, [$rental['rental_id'], $late_fee_amount, $days_late]);
        
        // Buat notifikasi untuk penyewa
        $item_sql = "SELECT i.name, u.user_id FROM items i 
                    JOIN rentals r ON i.item_id = r.item_id 
                    JOIN users u ON r.renter_id = u.user_id 
                    WHERE r.rental_id = ?";
        $item_info = fetchOne($item_sql, [$rental['rental_id']]);
        
        createNotification(
            $rental['renter_id'],
            "Keterlambatan Pengembalian",
            "Anda terlambat mengembalikan {$item_info['name']} selama {$days_late} hari. Denda sebesar Rp " . number_format($late_fee_amount, 0, ',', '.') . " telah dikenakan.",
            "late_fee",
            $rental['rental_id']
        );
        
        // Buat notifikasi untuk pemilik
        $owner_sql = "SELECT owner_id FROM items WHERE item_id = ?";
        $owner = fetchOne($owner_sql, [$rental['item_id']]);
        
        createNotification(
            $owner['owner_id'],
            "Keterlambatan Pengembalian",
            "Penyewa terlambat mengembalikan {$item_info['name']} selama {$days_late} hari.",
            "late_fee",
            $rental['rental_id']
        );
    }
}

// Fungsi untuk format tanggal menjadi format Indonesia
function formatDate($date) {
    $date = new DateTime($date);
    return $date->format('d/m/Y');
}

// Fungsi untuk format harga
function formatPrice($price) {
    return 'Rp ' . number_format($price, 0, ',', '.');
}


?>
<?php
// Hapus baris kosong atau spasi sebelum tag PHP
// Mulai session paling awal
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Tentukan base path untuk URL
$base_path = '';
$current_path = $_SERVER['PHP_SELF'];

// Jika file berada di subfolder (owner atau renter)
if (strpos($current_path, '/owner/') !== false || strpos($current_path, '/renter/') !== false) {
    $base_path = '../';
}

// Include file-file penting
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

// Cek pengembalian terlambat
if (isLoggedIn()) {
    checkLateReturns();
}
?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SewaBarangYuk</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="<?= $base_path ?>assets/css/style.css">
    <?php if (isLoggedIn()): ?>
    <script src="<?= $base_path ?>assets/js/notifications.js"></script>
    <?php endif; ?>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?= $base_path ?>index.php">SewaBarangYuk</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mr-auto">
                    <?php if (isLoggedIn()): ?>
                        <?php if (isRenter()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $base_path ?>renter/dashboard.php">Beranda</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $base_path ?>renter/view_items.php">Lihat Barang</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $base_path ?>renter/my_rentals.php">Penyewaan Saya</a>
                        </li>
                        <?php endif; ?>
                        
                        <?php if (isOwner()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $base_path ?>owner/dashboard.php">Beranda</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $base_path ?>owner/manage_items.php">Kelola Barang</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $base_path ?>owner/rental_requests.php">Permintaan Sewa</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $base_path ?>owner/active_rentals.php">Penyewaan Aktif</a>
                        </li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $base_path ?>index.php">Beranda</a>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="notificationDropdown" role="button" data-toggle="dropdown">
                                <i class="fas fa-bell"></i>
                                <?php 
                                $unread_count = getUnreadNotificationsCount($_SESSION['user_id']);
                                if ($unread_count > 0): 
                                ?>
                                <span class="badge badge-danger"><?= $unread_count ?></span>
                                <?php endif; ?>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="notificationDropdown">
                                <?php
                                $notifications = fetchAll("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5", [$_SESSION['user_id']]);
                                if (count($notifications) > 0):
                                    foreach ($notifications as $notification):
                                ?>
                                <a class="dropdown-item <?= $notification['is_read'] ? '' : 'font-weight-bold' ?>" href="#">
                                    <small class="text-muted"><?= date('d/m/Y H:i', strtotime($notification['created_at'])) ?></small><br>
                                    <?= $notification['title'] ?>
                                </a>
                                <?php 
                                    endforeach;
                                else:
                                ?>
                                <a class="dropdown-item" href="#">Tidak ada notifikasi</a>
                                <?php endif; ?>
                            </div>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php 
                                if (isRenter()) {
                                    echo $base_path . 'renter/profile.php';
                                } elseif (isOwner()) {
                                    echo $base_path . 'owner/profile.php';
                                }
                            ?>">
                                <?= htmlspecialchars($_SESSION['user_name']) ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $base_path ?>logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $base_path ?>login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $base_path ?>register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4"></div>
</body>
</html>
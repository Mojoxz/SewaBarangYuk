<?php
// Mulai session jika belum dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Fungsi untuk login
function login($email, $password) {
    $sql = "SELECT * FROM users WHERE email = ?";
    $user = fetchOne($sql, [$email]);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_type'] = $user['user_type'];
        return true;
    }
    
    return false;
}

// Fungsi untuk register
function register($name, $email, $password, $address, $phone, $user_type) {
    // Cek apakah email sudah digunakan
    $check_sql = "SELECT COUNT(*) as count FROM users WHERE email = ?";
    $result = fetchOne($check_sql, [$email]);
    
    if ($result['count'] > 0) {
        return [
            'success' => false,
            'message' => 'Email sudah terdaftar.'
        ];
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user baru
    $sql = "INSERT INTO users (name, email, password, address, phone, user_type) 
            VALUES (?, ?, ?, ?, ?, ?)";
    executeQuery($sql, [$name, $email, $hashed_password, $address, $phone, $user_type]);
    
    return [
        'success' => true,
        'user_id' => lastInsertId()
    ];
}

// Fungsi untuk memastikan user sudah login
function requireLogin() {
    if (!isLoggedIn()) {
        // Tentukan base path untuk redirect
        $base_path = '';
        $current_path = $_SERVER['PHP_SELF'];
        if (strpos($current_path, '/owner/') !== false || strpos($current_path, '/renter/') !== false) {
            $base_path = '../';
        }
        header("Location: {$base_path}login.php");
        exit();
    }
}

// Fungsi untuk memastikan user adalah pemilik
function requireOwner() {
    requireLogin();
    
    if (!isOwner()) {
        // Tentukan base path untuk redirect
        $base_path = '';
        $current_path = $_SERVER['PHP_SELF'];
        if (strpos($current_path, '/owner/') !== false || strpos($current_path, '/renter/') !== false) {
            $base_path = '../';
        }
        header("Location: {$base_path}index.php");
        exit();
    }
}

// Fungsi untuk memastikan user adalah penyewa
function requireRenter() {
    requireLogin();
    
    if (!isRenter()) {
        // Tentukan base path untuk redirect
        $base_path = '';
        $current_path = $_SERVER['PHP_SELF'];
        if (strpos($current_path, '/owner/') !== false || strpos($current_path, '/renter/') !== false) {
            $base_path = '../';
        }
        header("Location: {$base_path}index.php");
        exit();
    }
}

// Fungsi untuk logout
function logout() {
    session_unset();
    session_destroy();
}
?>
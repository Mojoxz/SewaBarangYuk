<?php
session_start();
require_once 'includes/auth.php';

// Logout user
logout();

// Alihkan ke halaman login dengan path yang benar
header("Location: login.php");
exit();
?>
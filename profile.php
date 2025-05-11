<?php
require_once 'includes/header.php';
requireLogin();

// Redirect ke halaman profil sesuai tipe user
if (isOwner() && !isRenter()) {
    redirect('owner/profile.php');
} else {
    redirect('renter/profile.php');
}
?>
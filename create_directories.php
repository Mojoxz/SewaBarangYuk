<?php
// Script untuk membuat direktori yang diperlukan

// Base directory
$baseUploadDir = __DIR__ . '/assets/images/uploads';

// Direktori yang perlu dibuat
$directories = [
    $baseUploadDir,
    $baseUploadDir . '/items',
    $baseUploadDir . '/id_cards',
    $baseUploadDir . '/profiles'
];

// Buat direktori jika belum ada
foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "Direktori $dir berhasil dibuat<br>";
        } else {
            echo "Gagal membuat direktori $dir<br>";
        }
    } else {
        echo "Direktori $dir sudah ada<br>";
    }
}

echo "<br>Selesai memeriksa direktori.";
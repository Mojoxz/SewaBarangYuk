<footer class="bg-dark text-white mt-5 py-4">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-3">
                <h5>SewaBarangYuk</h5>
                <p class="mb-1"><i class="fa fa-map-marker-alt mr-2"></i> Jl. Ahmad Yani No. 123</p>
                <p class="mb-1"><i class="fa fa-phone mr-2"></i> +62 812 3456 7890</p>
                <p class="mb-1"><i class="fa fa-envelope mr-2"></i> info@sewabarangyuk.com</p>
            </div>
            <div class="col-md-4 mb-3">
                <h5>Layanan Kami</h5>
                <ul class="list-unstyled">
                    <li><a class="text-white" href="<?= $base_path ?>index.php">Katalog Barang</a></li>
                    <li><a class="text-white" href="<?= $base_path ?>index.php">Cara Menyewa</a></li>
                    <li><a class="text-white" href="<?= $base_path ?>index.php">Hubungi Kami</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-3">
                <h5>Ikuti Kami</h5>
                <div class="social-icons">
                    <a href="#" class="text-white mr-3"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-white mr-3"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-white mr-3"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
        </div>
        <hr class="bg-light">
        <div class="row">
            <div class="col-md-12 text-center">
                <p class="mb-0">&copy; <?= date('Y') ?> SewaBarangYuk - All Rights Reserved</p>
            </div>
        </div>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<!-- Font Awesome untuk ikon -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<?php
// Tentukan base path untuk script
$base_path = '';
$current_path = $_SERVER['PHP_SELF'];
if (strpos($current_path, '/owner/') !== false || strpos($current_path, '/renter/') !== false) {
    $base_path = '../';
}
?>
<script src="<?= $base_path ?>assets/js/main.js"></script>
<script src="<?= $base_path ?>assets/js/notifications.js"></script>
<?php
require_once 'includes/header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Email dan password harus diisi.';
    } else {
        if (login($email, $password)) {
            // Redirect berdasarkan tipe user
            if (isOwner()) {
                redirect('owner/dashboard.php');
            } else {
                redirect('renter/dashboard.php');
            }
        } else {
            $error = 'Email atau password salah.';
        }
    }
}
?>

<!-- Custom styles for login page -->
<style>
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.12), 0 4px 8px rgba(0,0,0,0.06) !important;
        transition: all 0.3s ease;
    }
    
    .card {
        border: none;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
        border-radius: 10px;
        overflow: hidden;
        transition: all 0.3s;
        margin-bottom: 20px;
    }
    
    .login-header {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        padding: 3rem 2rem;
        color: white;
        border-radius: 10px;
        margin-bottom: 30px;
        text-align: center;
    }
    
    .card-header {
        background: linear-gradient(45deg, #f8f9fa, #e9ecef);
        border-bottom: none;
        padding: 15px 20px;
        font-weight: 600;
        color: #333;
    }
    
    .btn-primary {
        background: linear-gradient(45deg, #007bff, #0062cc);
        border: none;
        box-shadow: 0 2px 4px rgba(0, 123, 255, 0.3);
        transition: all 0.3s;
        padding: 10px 20px;
        border-radius: 5px;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 123, 255, 0.4);
    }
    
    .form-control {
        border-radius: 5px;
        padding: 10px 15px;
        border: 1px solid #e3e6f0;
    }
    
    .form-control:focus {
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        border-color: #bac8f3;
    }
    
    .input-group-text {
        background-color: #4e73df;
        color: white;
        border: none;
    }
    
    .login-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
        color: white;
    }
    
    .register-link {
        color: #4e73df;
        font-weight: 500;
        transition: all 0.3s;
    }
    
    .register-link:hover {
        color: #224abe;
        text-decoration: none;
    }
</style>

<div class="container-fluid py-4">
    <!-- Login Header -->
    <div class="login-header shadow">
        <div class="container">
            <i class="fas fa-user-circle login-icon"></i>
            <h1 class="display-4 font-weight-bold">Selamat Datang</h1>
            <p class="lead">Silahkan login untuk melanjutkan</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-6">
            <?php if ($error): ?>
            <div class="alert alert-danger shadow-sm">
                <i class="fas fa-exclamation-circle mr-2"></i> <?= $error ?>
            </div>
            <?php endif; ?>
            
            <div class="card hover-shadow">
                <div class="card-header">
                    <i class="fas fa-sign-in-alt mr-2"></i> Login
                </div>
                <div class="card-body">
                    <form method="post">
                        <div class="form-group">
                            <label for="email"><i class="fas fa-envelope mr-2 text-primary"></i>Email</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                </div>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="password"><i class="fas fa-lock mr-2 text-primary"></i>Password</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                </div>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </div>
                        
                        <div class="text-right mt-4">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-sign-in-alt mr-2"></i> Login
                            </button>
                        </div>
                    </form>
                    
                    <div class="mt-4 text-center">
                        <p>Belum punya akun? <a href="register.php" class="register-link">Daftar disini</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
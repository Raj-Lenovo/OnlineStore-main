<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

$pageTitle = 'Admin Login';

// If already logged in:
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('dashboard.php');
    } else {
        $_SESSION['error'] = 'You are logged in as a customer, not an admin.';
        redirect('../index.php');
    }
}

// Handle admin login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $result = loginUser($username, $password);

    if ($result['success']) {
        if (isAdmin()) {
            $_SESSION['success'] = 'Welcome, admin!';
            redirect('dashboard.php');
        } else {
            // Logged in, but not an admin
            logoutUser();
            $_SESSION['error'] = 'This account is not an admin user.';
        }
    } else {
        $_SESSION['error'] = $result['message'] ?? 'Invalid username or password.';
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h3 class="text-center mb-0">Admin Login</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username or Email</label>
                        <input
                            type="text"
                            class="form-control"
                            id="username"
                            name="username"
                            required
                            autofocus
                        >
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input
                            type="password"
                            class="form-control"
                            id="password"
                            name="password"
                            required
                        >
                    </div>
                    <button type="submit" class="btn btn-dark w-100">Login as Admin</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

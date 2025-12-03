<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Login';

// Already logged in? redirect based on role
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('admin/dashboard.php');
    } else {
        redirect('index.php');
    }
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $result = loginUser($username, $password);

    if ($result['success']) {
        $_SESSION['success'] = $result['message'] ?? 'Login successful.';

        if (isAdmin()) {
            // Logged in as admin
            redirect('admin/dashboard.php');
        } else {
            // Logged in as normal user
            redirect('index.php');
        }
    } else {
        $_SESSION['error'] = $result['message'] ?? 'Invalid username or password.';
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">
                <h3 class="text-center">Login</h3>
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
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

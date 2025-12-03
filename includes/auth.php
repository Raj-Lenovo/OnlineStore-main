<?php
// includes/auth.php – shared authentication helpers

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config.php';  // getDB(), isLoggedIn(), isAdmin(), redirect()

/**
 * Register a new user (optional)
 */
function registerUser(
    string $username,
    string $email,
    string $password,
    string $full_name,
    string $address = '',
    string $phone = ''
): array {
    $pdo = getDB();

    if ($username === '' || $email === '' || $password === '' || $full_name === '') {
        return ['success' => false, 'message' => 'All required fields must be filled.'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Invalid email address.'];
    }

    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'Password must be at least 6 characters long.'];
    }

    try {
        // Check if username or email already exists
        $stmt = $pdo->prepare(
            "SELECT id FROM users WHERE username = ? OR email = ?"
        );
        $stmt->execute([$username, $email]);

        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Username or email already exists.'];
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Assuming 'role' column has a default (e.g. 'customer')
        $stmt = $pdo->prepare(
            "INSERT INTO users (username, email, password, full_name, address, phone)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$username, $email, $hashedPassword, $full_name, $address, $phone]);

        return ['success' => true, 'message' => 'Registration successful. Please login.'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
    }
}

/**
 * Login a user (username OR email). Works for both customers and admins.
 */
function loginUser(string $username, string $password): array
{
    $pdo = getDB();

    if ($username === '' || $password === '') {
        return ['success' => false, 'message' => 'Username and password are required.'];
    }

    try {
        // NOTE: using positional placeholders to avoid HY093
        $stmt = $pdo->prepare(
            "SELECT id, username, email, password, role
             FROM users
             WHERE username = ? OR email = ?
             LIMIT 1"
        );
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return ['success' => false, 'message' => 'Invalid username or password.'];
        }

        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Invalid username or password.'];
        }

        // Store session data – matches config.php helpers
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['user_role'] = $user['role'];   // 'admin' or 'customer'

        return [
            'success' => true,
            'message' => 'Login successful!',
            'role'    => $user['role']
        ];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Login failed: ' . $e->getMessage()];
    }
}

/**
 * Logout current user
 */
function logoutUser(): bool
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION = [];
        session_unset();
        session_destroy();
    }
    return true;
}

/**
 * Get current user (basic info)
 */
function getCurrentUser(): ?array
{
    if (!isLoggedIn()) {
        return null;
    }

    $pdo = getDB();

    try {
        $stmt = $pdo->prepare(
            "SELECT id, username, email, role
             FROM users
             WHERE id = ?"
        );
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    } catch (PDOException $e) {
        return null;
    }
}

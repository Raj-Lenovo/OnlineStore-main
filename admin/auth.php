<?php
// admin/auth.php – glue for admin pages

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

/**
 * Call this at top of any admin-only page (e.g., dashboard.php)
 * It uses requireAdmin() from config.php
 */
function requireAdminAuth(): void
{
    requireAdmin(); // already defined in includes/config.php
}

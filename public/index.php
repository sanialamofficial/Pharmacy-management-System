<?php
declare(strict_types=1);

session_start();

if (!function_exists('collect_initials')) {
    function collect_initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name));
        $out = '';
        foreach ($parts as $p) {
            if ($p !== '') {
                $out .= mb_strtoupper(mb_substr($p, 0, 1));
            }
        }
        return mb_substr($out, 0, 2);
    }
}

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = __DIR__ . '/../app/' . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\StaffController;
use App\Core\Router;

$router = new Router();

// Public & Auth
$router->get('/', [HomeController::class, 'index']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);

// Admin Console
$router->get('/admin', [AdminController::class, 'dashboard']);
$router->get('/admin/inventory', [AdminController::class, 'inventory']);
$router->post('/admin/inventory/store', [AdminController::class, 'storeMedicine']);
$router->post('/admin/inventory/update/{id}', [AdminController::class, 'updateMedicine']);
$router->post('/admin/inventory/restock/{id}', [AdminController::class, 'restockMedicine']);
$router->post('/admin/inventory/delete/{id}', [AdminController::class, 'deleteMedicine']);

$router->get('/admin/staff', [AdminController::class, 'staff']);
$router->post('/admin/staff/store', [AdminController::class, 'storeStaff']);
$router->post('/admin/staff/toggle/{id}', [AdminController::class, 'toggleStaff']);
$router->post('/admin/staff/reset-password/{id}', [AdminController::class, 'resetStaffPassword']);
$router->post('/admin/staff/delete/{id}', [AdminController::class, 'deleteStaff']);

$router->get('/admin/sales', [AdminController::class, 'sales']);
$router->get('/admin/sales/export', [AdminController::class, 'exportSalesCsv']);
$router->get('/admin/checkout', [AdminController::class, 'checkout']);
$router->get('/admin/settings', [AdminController::class, 'settings']);

// Staff Counter
$router->get('/staff', [StaffController::class, 'dashboard']);
$router->get('/staff/checkout', [StaffController::class, 'checkout']);
$router->get('/staff/sales', [StaffController::class, 'sales']);
$router->get('/staff/sales/export', [StaffController::class, 'exportSalesCsv']);
$router->get('/staff/settings', [StaffController::class, 'settings']);

// Shared Actions & APIs
$router->post('/settings/profile', [AdminController::class, 'updateProfile']);
$router->post('/settings/password', [AdminController::class, 'updatePassword']);
$router->post('/checkout/complete', [StaffController::class, 'completeCheckout']);
$router->get('/api/receipt/{id}', [StaffController::class, 'receiptJson']);
$router->post('/api/notifications/read-all', [AdminController::class, 'markNotificationsRead']);

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

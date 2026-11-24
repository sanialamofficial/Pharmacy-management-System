<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Medicine;
use App\Models\Notification;
use App\Models\Sale;
use App\Models\User;

class AdminController extends Controller
{
    public function dashboard(): void
    {
        $user = $this->requireRole('admin');
        $period = $_GET['period'] ?? 'week';
        if (!in_array($period, ['today', 'week', 'month'], true)) {
            $period = 'week';
        }

        $saleModel = new Sale();
        $medModel = new Medicine();
        $notifModel = new Notification();

        $this->view('admin/dashboard', [
            'pageTitle' => 'Pharmacy overview — Dispensa',
            'activeNav' => 'dashboard',
            'period' => $period,
            'kpis' => $saleModel->getPeriodKpis($period),
            'revenueByDay' => $saleModel->getRevenueByDay(7),
            'lowStock' => $medModel->getLowStock($this->config['low_stock_threshold'], 5),
            'salesByStaff' => $saleModel->getSalesByStaff($period),
            'topMedicines' => $saleModel->getTopMedicines($period, null, 5),
            'expiringSoon' => $medModel->getExpiringSoon($this->config['expiry_warning_days'], 5),
            'latestBills' => $saleModel->getLatestBills(null, 5),
            'alerts' => $notifModel->getAlertsForUser((int)$user['id'], $this->config['low_stock_threshold'], $this->config['expiry_warning_days']),
        ]);
    }

    public function inventory(): void
    {
        $user = $this->requireRole('admin');
        $filters = [
            'q' => $_GET['q'] ?? '',
            'category' => $_GET['category'] ?? 'All',
            'quick' => $_GET['quick'] ?? 'all',
        ];

        $medModel = new Medicine();
        $notifModel = new Notification();

        $this->view('admin/inventory', [
            'pageTitle' => 'Inventory — Dispensa',
            'activeNav' => 'inventory',
            'filters' => $filters,
            'summary' => $medModel->getSummary($this->config['low_stock_threshold']),
            'medicines' => $medModel->getAll($filters, $this->config['low_stock_threshold'], $this->config['expiry_warning_days']),
            'categories' => Medicine::CATEGORIES,
            'forms' => Medicine::FORMS,
            'alerts' => $notifModel->getAlertsForUser((int)$user['id'], $this->config['low_stock_threshold'], $this->config['expiry_warning_days']),
        ]);
    }

    public function storeMedicine(): void
    {
        $this->requireRole('admin');
        $medModel = new Medicine();
        $medModel->create($_POST);
        $this->setFlash('success', 'Medicine added to inventory.');
        $this->redirect('/admin/inventory');
    }

    public function updateMedicine(string $id): void
    {
        $this->requireRole('admin');
        $medModel = new Medicine();
        $medModel->update((int)$id, $_POST);
        $this->setFlash('success', 'Medicine details updated.');
        $this->redirect('/admin/inventory');
    }

    public function restockMedicine(string $id): void
    {
        $this->requireRole('admin');
        $units = (int)($_POST['units'] ?? 0);
        $medModel = new Medicine();
        if ($medModel->restock((int)$id, $units)) {
            $this->setFlash('success', "Added {$units} units to stock.");
        } else {
            $this->setFlash('error', 'Please enter a valid restock quantity.');
        }
        $returnTo = $_POST['return_to'] ?? '/admin/inventory';
        $this->redirect($returnTo);
    }

    public function deleteMedicine(string $id): void
    {
        $this->requireRole('admin');
        $medModel = new Medicine();
        if ($medModel->delete((int)$id)) {
            $this->setFlash('success', 'Medicine removed from inventory.');
        } else {
            $this->setFlash('error', 'Cannot delete a medicine that appears in past sales invoices.');
        }
        $this->redirect('/admin/inventory');
    }

    public function staff(): void
    {
        $user = $this->requireRole('admin');
        $userModel = new User();
        $notifModel = new Notification();

        $this->view('admin/staff', [
            'pageTitle' => 'Staff — Dispensa',
            'activeNav' => 'staff',
            'staffList' => $userModel->getStaffWithStats(),
            'alerts' => $notifModel->getAlertsForUser((int)$user['id'], $this->config['low_stock_threshold'], $this->config['expiry_warning_days']),
        ]);
    }

    public function storeStaff(): void
    {
        $this->requireRole('admin');
        $userModel = new User();
        try {
            $userModel->createStaff($_POST);
            $this->setFlash('success', 'Staff account created (default password: greenleaf).');
        } catch (\Throwable $e) {
            $this->setFlash('error', 'Could not create staff account. Email may already exist.');
        }
        $this->redirect('/admin/staff');
    }

    public function toggleStaff(string $id): void
    {
        $this->requireRole('admin');
        $userModel = new User();
        $userModel->toggleStatus((int)$id);
        $this->setFlash('success', 'Staff account status updated.');
        $this->redirect('/admin/staff');
    }

    public function resetStaffPassword(string $id): void
    {
        $this->requireRole('admin');
        $newPass = trim($_POST['new_password'] ?? 'greenleaf');
        if (strlen($newPass) < 6) {
            $this->setFlash('error', 'Password must be at least 6 characters.');
            $this->redirect('/admin/staff');
        }
        $userModel = new User();
        $userModel->resetPassword((int)$id, $newPass);
        $this->setFlash('success', 'Staff password has been reset.');
        $this->redirect('/admin/staff');
    }

    public function deleteStaff(string $id): void
    {
        $this->requireRole('admin');
        $userModel = new User();
        $userModel->deleteStaff((int)$id);
        $this->setFlash('success', 'Staff member removed or suspended (if sales history exists).');
        $this->redirect('/admin/staff');
    }

    public function sales(): void
    {
        $user = $this->requireRole('admin');
        $filters = [
            'q' => $_GET['q'] ?? '',
            'user_id' => $_GET['user_id'] ?? '',
            'range' => $_GET['range'] ?? '30d',
            'payment' => $_GET['payment'] ?? 'All',
        ];

        $saleModel = new Sale();
        $userModel = new User();
        $notifModel = new Notification();

        $sales = $saleModel->getFilteredSales($filters);
        $totalRev = array_sum(array_column($sales, 'total_amount'));
        $count = count($sales);

        $this->view('admin/sales', [
            'pageTitle' => 'Sales records — Dispensa',
            'activeNav' => 'sales',
            'isStaffView' => false,
            'filters' => $filters,
            'sales' => $sales,
            'summary' => [
                'bills' => $count,
                'revenue' => round($totalRev, 2),
                'avg_bill' => $count > 0 ? round($totalRev / $count, 2) : 0.0,
            ],
            'staffFilterList' => $userModel->getAllStaffForFilter(),
            'alerts' => $notifModel->getAlertsForUser((int)$user['id'], $this->config['low_stock_threshold'], $this->config['expiry_warning_days']),
        ]);
    }

    public function exportSalesCsv(): void
    {
        $this->requireRole('admin');
        $filters = [
            'q' => $_GET['q'] ?? '',
            'user_id' => $_GET['user_id'] ?? '',
            'range' => $_GET['range'] ?? '30d',
            'payment' => $_GET['payment'] ?? 'All',
        ];
        $saleModel = new Sale();
        $sales = $saleModel->getFilteredSales($filters);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="dispensa-sales-records.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Invoice', 'Date', 'Served By', 'Customer', 'Items', 'Payment', 'Discount %', 'Total']);
        foreach ($sales as $s) {
            fputcsv($out, [
                $s['invoice_no'],
                $s['created_at'],
                $s['staff_name'],
                $s['customer_name'],
                $s['item_count'],
                $s['payment_method'],
                $s['discount_pct'] . '%',
                number_format((float)$s['total_amount'], 2),
            ]);
        }
        fclose($out);
        exit;
    }

    public function checkout(): void
    {
        $user = $this->requireRole('admin');
        $medModel = new Medicine();
        $notifModel = new Notification();

        $this->view('shared/checkout', [
            'pageTitle' => 'Counter checkout — Dispensa',
            'activeNav' => 'checkout',
            'medicines' => $medModel->getAll([], $this->config['low_stock_threshold'], $this->config['expiry_warning_days']),
            'categories' => Medicine::CATEGORIES,
            'alerts' => $notifModel->getAlertsForUser((int)$user['id'], $this->config['low_stock_threshold'], $this->config['expiry_warning_days']),
        ]);
    }

    public function settings(): void
    {
        $user = $this->requireRole('admin');
        $userModel = new User();
        $notifModel = new Notification();

        $this->view('shared/settings', [
            'pageTitle' => 'Profile & security — Dispensa',
            'activeNav' => 'settings',
            'profile' => $userModel->findById((int)$user['id']),
            'alerts' => $notifModel->getAlertsForUser((int)$user['id'], $this->config['low_stock_threshold'], $this->config['expiry_warning_days']),
        ]);
    }

    public function updateProfile(): void
    {
        $user = $this->requireAuth();
        $userModel = new User();
        $userModel->updateProfile((int)$user['id'], $_POST);

        $updated = $userModel->findById((int)$user['id']);
        if ($updated) {
            unset($updated['password_hash']);
            $_SESSION['user'] = $updated;
        }
        $this->setFlash('success', 'Profile details saved.');
        $this->redirect($user['role'] === 'admin' ? '/admin/settings' : '/staff/settings');
    }

    public function updatePassword(): void
    {
        $user = $this->requireAuth();
        $userModel = new User();
        $fullUser = $userModel->findById((int)$user['id']);

        $current = $_POST['current_password'] ?? '';
        $newPass = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $back = $user['role'] === 'admin' ? '/admin/settings' : '/staff/settings';

        if (!$fullUser || !password_verify($current, $fullUser['password_hash'])) {
            $this->setFlash('error', 'Current password is incorrect.');
            $this->redirect($back);
        }

        if ($newPass !== $confirm) {
            $this->setFlash('error', 'New passwords do not match.');
            $this->redirect($back);
        }

        if (strlen($newPass) < 8 || !preg_match('/\d/', $newPass) || !preg_match('/[a-z]/', $newPass) || !preg_match('/[A-Z]/', $newPass)) {
            $this->setFlash('error', 'Password must be at least 8 characters and include a number, uppercase, and lowercase letter.');
            $this->redirect($back);
        }

        $userModel->updatePassword((int)$user['id'], $newPass);
        $this->setFlash('success', 'Password updated successfully.');
        $this->redirect($back);
    }

    public function markNotificationsRead(): void
    {
        $user = $this->requireRole('admin');
        $notifModel = new Notification();
        $alerts = $notifModel->getAlertsForUser((int)$user['id'], $this->config['low_stock_threshold'], $this->config['expiry_warning_days']);
        $keys = array_column($alerts, 'key');
        $notifModel->markAllRead((int)$user['id'], $keys);
        $this->json(['ok' => true]);
    }
}

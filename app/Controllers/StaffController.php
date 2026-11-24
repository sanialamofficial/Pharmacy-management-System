<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Medicine;
use App\Models\Sale;
use App\Models\User;

class StaffController extends Controller
{
    public function dashboard(): void
    {
        $user = $this->requireRole('staff');
        $period = $_GET['period'] ?? 'daily';
        if (!in_array($period, ['daily', 'weekly', 'monthly'], true)) {
            $period = 'daily';
        }

        $saleModel = new Sale();
        $userId = (int)$user['id'];

        $this->view('staff/dashboard', [
            'pageTitle' => 'My dashboard — Dispensa',
            'activeNav' => 'dashboard',
            'period' => $period,
            'kpis' => $saleModel->getPeriodKpis($period, $userId),
            'hourlySales' => $saleModel->getStaffRevenueByHour($userId),
            'teamRank' => $saleModel->getStaffTeamRank($userId),
            'glance' => [
                'today' => $saleModel->getPeriodKpis('today', $userId),
                'week' => $saleModel->getPeriodKpis('week', $userId),
                'month' => $saleModel->getPeriodKpis('month', $userId),
            ],
            'recentBills' => $saleModel->getLatestBills($userId, 5),
            'topSellers' => $saleModel->getTopMedicines($period, $userId, 5),
        ]);
    }

    public function checkout(): void
    {
        $this->requireRole('staff');
        $medModel = new Medicine();

        $this->view('shared/checkout', [
            'pageTitle' => 'New bill — Dispensa',
            'activeNav' => 'checkout',
            'medicines' => $medModel->getAll([], $this->config['low_stock_threshold'], $this->config['expiry_warning_days']),
            'categories' => Medicine::CATEGORIES,
            'alerts' => [],
        ]);
    }

    public function sales(): void
    {
        $user = $this->requireRole('staff');
        $filters = [
            'q' => $_GET['q'] ?? '',
            'user_id' => (int)$user['id'],
            'range' => $_GET['range'] ?? '30d',
            'payment' => $_GET['payment'] ?? 'All',
        ];

        $saleModel = new Sale();
        $sales = $saleModel->getFilteredSales($filters);
        $totalRev = array_sum(array_column($sales, 'total_amount'));
        $count = count($sales);

        $this->view('admin/sales', [
            'pageTitle' => 'My sales — Dispensa',
            'activeNav' => 'sales',
            'isStaffView' => true,
            'filters' => $filters,
            'sales' => $sales,
            'summary' => [
                'bills' => $count,
                'revenue' => round($totalRev, 2),
                'avg_bill' => $count > 0 ? round($totalRev / $count, 2) : 0.0,
            ],
            'staffFilterList' => [],
            'alerts' => [],
        ]);
    }

    public function exportSalesCsv(): void
    {
        $user = $this->requireRole('staff');
        $filters = [
            'q' => $_GET['q'] ?? '',
            'user_id' => (int)$user['id'],
            'range' => $_GET['range'] ?? '30d',
            'payment' => $_GET['payment'] ?? 'All',
        ];
        $saleModel = new Sale();
        $sales = $saleModel->getFilteredSales($filters);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="dispensa-my-sales.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Invoice', 'Date', 'Customer', 'Items', 'Payment', 'Discount %', 'Total']);
        foreach ($sales as $s) {
            fputcsv($out, [
                $s['invoice_no'],
                $s['created_at'],
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

    public function settings(): void
    {
        $user = $this->requireRole('staff');
        $userModel = new User();

        $this->view('shared/settings', [
            'pageTitle' => 'Profile & security — Dispensa',
            'activeNav' => 'settings',
            'profile' => $userModel->findById((int)$user['id']),
            'alerts' => [],
        ]);
    }

    public function completeCheckout(): void
    {
        $user = $this->requireAuth();
        $raw = file_get_contents('php://input');
        $payload = json_decode($raw ?: '', true);

        if (!is_array($payload)) {
            $this->json(['error' => 'Invalid request payload.'], 400);
        }

        try {
            $saleModel = new Sale();
            $receipt = $saleModel->createSale((int)$user['id'], $payload);
            $this->json(['ok' => true, 'receipt' => $receipt]);
        } catch (\Throwable $e) {
            $this->json(['error' => $e->getMessage()], 422);
        }
    }

    public function receiptJson(string $id): void
    {
        $user = $this->requireAuth();
        $saleModel = new Sale();
        $receipt = $saleModel->getReceiptById((int)$id);

        if (!$receipt) {
            $this->json(['error' => 'Receipt not found.'], 404);
        }

        if ($user['role'] !== 'admin' && (int)$receipt['user_id'] !== (int)$user['id']) {
            $this->json(['error' => 'Unauthorized.'], 403);
        }

        $this->json(['ok' => true, 'receipt' => $receipt]);
    }
}

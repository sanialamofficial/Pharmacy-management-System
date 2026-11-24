<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Sale extends Model
{
    public function getPeriodKpis(string $period = 'week', ?int $userId = null): array
    {
        [$curStart, $curEnd, $prevStart, $prevEnd] = $this->getDateWindows($period);

        $cur = $this->aggregateWindow($curStart, $curEnd, $userId);
        $prev = $this->aggregateWindow($prevStart, $prevEnd, $userId);

        return [
            'revenue' => $cur['revenue'],
            'revenue_delta' => $this->pctDelta($cur['revenue'], $prev['revenue']),
            'bills' => $cur['bills'],
            'bills_delta' => $this->pctDelta($cur['bills'], $prev['bills']),
            'avg_bill' => $cur['avg_bill'],
            'avg_bill_delta' => $this->pctDelta($cur['avg_bill'], $prev['avg_bill']),
            'units' => $cur['units'],
            'units_delta' => $this->pctDelta($cur['units'], $prev['units']),
        ];
    }

    private function getDateWindows(string $period): array
    {
        return match ($period) {
            'today', 'daily' => [
                date('Y-m-d 00:00:00'),
                date('Y-m-d 23:59:59'),
                date('Y-m-d 00:00:00', strtotime('-1 day')),
                date('Y-m-d 23:59:59', strtotime('-1 day')),
            ],
            'month', 'monthly' => [
                date('Y-m-d 00:00:00', strtotime('-29 days')),
                date('Y-m-d 23:59:59'),
                date('Y-m-d 00:00:00', strtotime('-59 days')),
                date('Y-m-d 23:59:59', strtotime('-30 days')),
            ],
            default => [
                date('Y-m-d 00:00:00', strtotime('-6 days')),
                date('Y-m-d 23:59:59'),
                date('Y-m-d 00:00:00', strtotime('-13 days')),
                date('Y-m-d 23:59:59', strtotime('-7 days')),
            ],
        };
    }

    private function aggregateWindow(string $start, string $end, ?int $userId = null): array
    {
        $userClause = $userId !== null ? ' AND s.user_id = ?' : '';
        $params = [$start, $end];
        if ($userId !== null) {
            $params[] = $userId;
        }

        $sql = "
            SELECT
                COUNT(DISTINCT s.id) AS bills,
                COALESCE(SUM(s.total_amount), 0) AS revenue
            FROM sales s
            WHERE s.created_at BETWEEN ? AND ? {$userClause}
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();

        $unitSql = "
            SELECT COALESCE(SUM(si.qty), 0) AS units
            FROM sale_items si
            JOIN sales s ON s.id = si.sale_id
            WHERE s.created_at BETWEEN ? AND ? {$userClause}
        ";
        $uStmt = $this->db->prepare($unitSql);
        $uStmt->execute($params);
        $units = (int)$uStmt->fetchColumn();

        $bills = (int)$row['bills'];
        $revenue = round((float)$row['revenue'], 2);
        $avg = $bills > 0 ? round($revenue / $bills, 2) : 0.0;

        return [
            'bills' => $bills,
            'revenue' => $revenue,
            'avg_bill' => $avg,
            'units' => $units,
        ];
    }

    private function pctDelta(float|int $current, float|int $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100.0 : 0.0;
        }
        return round((($current - $previous) / $previous) * 100, 1);
    }

    public function getRevenueByDay(int $days = 7): array
    {
        $result = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-{$i} days"));
            $label = date('D', strtotime($d));
            $stmt = $this->db->prepare('SELECT COALESCE(SUM(total_amount), 0) FROM sales WHERE date(created_at) = ?');
            $stmt->execute([$d]);
            $result[] = [
                'date' => $d,
                'label' => $label,
                'revenue' => round((float)$stmt->fetchColumn(), 2),
            ];
        }
        return $result;
    }

    public function getStaffRevenueByHour(int $userId): array
    {
        $hours = range(8, 21); // 8a to 9p
        $stmt = $this->db->prepare("
            SELECT CAST(strftime('%H', created_at) AS INTEGER) AS hr, COALESCE(SUM(total_amount), 0) AS rev
            FROM sales
            WHERE user_id = ? AND created_at >= datetime('now', '-7 days')
            GROUP BY hr
        ");
        $stmt->execute([$userId]);
        $map = [];
        foreach ($stmt->fetchAll() as $r) {
            $map[(int)$r['hr']] = round((float)$r['rev'], 2);
        }

        $out = [];
        foreach ($hours as $h) {
            $label = $h === 12 ? '12p' : ($h > 12 ? ($h - 12) . 'p' : $h . 'a');
            $out[] = [
                'hour' => $h,
                'label' => $label,
                'revenue' => $map[$h] ?? 0.0,
            ];
        }
        return $out;
    }

    public function getStaffTeamRank(int $userId): array
    {
        $sql = "
            SELECT u.id, COALESCE(SUM(s.total_amount), 0) AS rev
            FROM users u
            LEFT JOIN sales s ON s.user_id = u.id AND s.created_at >= datetime('now', '-7 days')
            WHERE u.role = 'staff' AND u.status = 'active'
            GROUP BY u.id
            ORDER BY rev DESC
        ";
        $rows = $this->db->query($sql)->fetchAll();
        $rank = 1;
        $total = count($rows);
        foreach ($rows as $idx => $r) {
            if ((int)$r['id'] === $userId) {
                $rank = $idx + 1;
                break;
            }
        }
        return ['rank' => $rank, 'total' => max(1, $total)];
    }

    public function getSalesByStaff(string $period = 'week'): array
    {
        [$start, $end] = $this->getDateWindows($period);
        $sql = "
            SELECT
                u.id,
                u.full_name,
                u.status,
                COUNT(s.id) AS bills,
                COALESCE(SUM(s.total_amount), 0) AS revenue
            FROM users u
            LEFT JOIN sales s ON s.user_id = u.id AND s.created_at BETWEEN ? AND ?
            WHERE u.role = 'staff'
            GROUP BY u.id
            ORDER BY revenue DESC, u.full_name ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$start, $end]);
        $rows = $stmt->fetchAll();

        $totalRev = array_sum(array_column($rows, 'revenue'));
        foreach ($rows as &$r) {
            $rev = (float)$r['revenue'];
            $bills = (int)$r['bills'];
            $r['avg_bill'] = $bills > 0 ? round($rev / $bills, 2) : 0.0;
            $r['share_pct'] = $totalRev > 0 ? round(($rev / $totalRev) * 100, 1) : 0.0;
        }
        return $rows;
    }

    public function getTopMedicines(string $period = 'week', ?int $userId = null, int $limit = 5): array
    {
        [$start, $end] = $this->getDateWindows($period);
        $userClause = $userId !== null ? ' AND s.user_id = ?' : '';
        $params = [$start, $end];
        if ($userId !== null) {
            $params[] = $userId;
        }
        $params[] = $limit;

        $sql = "
            SELECT
                si.medicine_name,
                SUM(si.qty) AS total_qty,
                ROUND(SUM(si.line_total), 2) AS total_revenue
            FROM sale_items si
            JOIN sales s ON s.id = si.sale_id
            WHERE s.created_at BETWEEN ? AND ? {$userClause}
            GROUP BY si.medicine_id, si.medicine_name
            ORDER BY total_revenue DESC
            LIMIT ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getLatestBills(?int $userId = null, int $limit = 5): array
    {
        $where = $userId !== null ? 'WHERE s.user_id = ?' : '';
        $params = $userId !== null ? [$userId, $limit] : [$limit];

        $sql = "
            SELECT
                s.*,
                u.full_name AS staff_name,
                (SELECT COUNT(*) FROM sale_items si WHERE si.sale_id = s.id) AS item_count
            FROM sales s
            JOIN users u ON u.id = s.user_id
            {$where}
            ORDER BY s.created_at DESC, s.id DESC
            LIMIT ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getFilteredSales(array $filters = []): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['user_id'])) {
            $where[] = 's.user_id = ?';
            $params[] = (int)$filters['user_id'];
        }

        if (!empty($filters['payment']) && $filters['payment'] !== 'All') {
            $where[] = 's.payment_method = ?';
            $params[] = $filters['payment'];
        }

        $range = $filters['range'] ?? '30d';
        if ($range === 'today') {
            $where[] = "date(s.created_at) = date('now')";
        } elseif ($range === '7d') {
            $where[] = "s.created_at >= datetime('now', '-7 days')";
        } elseif ($range === '30d') {
            $where[] = "s.created_at >= datetime('now', '-30 days')";
        }

        if (!empty($filters['q'])) {
            $q = '%' . trim($filters['q']) . '%';
            $where[] = '(s.invoice_no LIKE ? OR s.customer_name LIKE ? OR EXISTS (SELECT 1 FROM sale_items si WHERE si.sale_id = s.id AND si.medicine_name LIKE ?))';
            array_push($params, $q, $q, $q);
        }

        $sql = "
            SELECT
                s.*,
                u.full_name AS staff_name,
                (SELECT COUNT(*) FROM sale_items si WHERE si.sale_id = s.id) AS item_count
            FROM sales s
            JOIN users u ON u.id = s.user_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY s.created_at DESC, s.id DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getReceiptById(int $saleId): ?array
    {
        $stmt = $this->db->prepare('
            SELECT s.*, u.full_name AS staff_name
            FROM sales s
            JOIN users u ON u.id = s.user_id
            WHERE s.id = ?
            LIMIT 1
        ');
        $stmt->execute([$saleId]);
        $sale = $stmt->fetch();
        if (!$sale) {
            return null;
        }

        $itemStmt = $this->db->prepare('SELECT * FROM sale_items WHERE sale_id = ? ORDER BY id ASC');
        $itemStmt->execute([$saleId]);
        $sale['items'] = $itemStmt->fetchAll();

        return $sale;
    }

    public function createSale(int $userId, array $payload): array
    {
        $items = $payload['items'] ?? [];
        if (empty($items)) {
            throw new \InvalidArgumentException('Cart cannot be empty.');
        }

        $this->db->beginTransaction();
        try {
            $subtotal = 0.0;
            $hasRx = false;
            $resolvedItems = [];

            $medStmt = $this->db->prepare('SELECT * FROM medicines WHERE id = ? LIMIT 1');
            $decStmt = $this->db->prepare('UPDATE medicines SET stock_qty = stock_qty - ? WHERE id = ? AND stock_qty >= ?');

            foreach ($items as $item) {
                $medId = (int)($item['id'] ?? 0);
                $qty = (int)($item['qty'] ?? 0);
                if ($medId <= 0 || $qty <= 0) {
                    continue;
                }
                $medStmt->execute([$medId]);
                $med = $medStmt->fetch();
                if (!$med) {
                    throw new \RuntimeException('Medicine not found.');
                }
                if ((int)$med['stock_qty'] < $qty) {
                    throw new \RuntimeException("Insufficient stock for {$med['brand_name']}.");
                }

                $decStmt->execute([$qty, $medId, $qty]);
                if ($decStmt->rowCount() === 0) {
                    throw new \RuntimeException("Stock update failed for {$med['brand_name']}.");
                }

                if ((int)$med['rx_required'] === 1) {
                    $hasRx = true;
                }

                $lineTotal = round((float)$med['unit_price'] * $qty, 2);
                $subtotal += $lineTotal;

                $resolvedItems[] = [
                    'medicine_id' => $medId,
                    'medicine_name' => $med['brand_name'] . ' ' . $med['strength'],
                    'qty' => $qty,
                    'unit_price' => (float)$med['unit_price'],
                    'line_total' => $lineTotal,
                ];
            }

            if (empty($resolvedItems)) {
                throw new \InvalidArgumentException('No valid items in cart.');
            }

            if ($hasRx && empty($payload['rx_checked'])) {
                throw new \InvalidArgumentException('Prescription verification is required for Rx medicines.');
            }

            $discountPct = max(0, min(50, (int)($payload['discount_pct'] ?? 0)));
            $subtotal = round($subtotal, 2);
            $discountAmount = round($subtotal * ($discountPct / 100), 2);
            $totalAmount = round($subtotal - $discountAmount, 2);

            $payment = in_array($payload['payment_method'] ?? 'Cash', ['Cash', 'Card', 'Mobile'], true)
                ? $payload['payment_method']
                : 'Cash';

            $lastId = (int)$this->db->query('SELECT COALESCE(MAX(id), 0) FROM sales')->fetchColumn();
            $invoiceNo = sprintf('INV-%05d', 900 + $lastId + 1);

            $saleStmt = $this->db->prepare('
                INSERT INTO sales (invoice_no, user_id, customer_name, customer_phone, payment_method, discount_pct, subtotal, discount_amount, total_amount, rx_checked, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');
            $saleStmt->execute([
                $invoiceNo,
                $userId,
                trim($payload['customer_name'] ?? '') ?: 'Walk-in',
                trim($payload['customer_phone'] ?? ''),
                $payment,
                $discountPct,
                $subtotal,
                $discountAmount,
                $totalAmount,
                $hasRx ? 1 : 0,
                date('Y-m-d H:i:s'),
            ]);
            $saleId = (int)$this->db->lastInsertId();

            $itemStmt = $this->db->prepare('
                INSERT INTO sale_items (sale_id, medicine_id, medicine_name, qty, unit_price, line_total)
                VALUES (?, ?, ?, ?, ?, ?)
            ');
            foreach ($resolvedItems as $ri) {
                $itemStmt->execute([
                    $saleId,
                    $ri['medicine_id'],
                    $ri['medicine_name'],
                    $ri['qty'],
                    $ri['unit_price'],
                    $ri['line_total'],
                ]);
            }

            $this->db->commit();
            return $this->getReceiptById($saleId) ?? ['id' => $saleId, 'invoice_no' => $invoiceNo];
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}

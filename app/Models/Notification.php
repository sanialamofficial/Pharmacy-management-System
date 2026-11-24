<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Notification extends Model
{
    public function getAlertsForUser(int $userId, int $threshold = 5, int $expiryDays = 30): array
    {
        $readStmt = $this->db->prepare('SELECT alert_key FROM notification_reads WHERE user_id = ?');
        $readStmt->execute([$userId]);
        $readKeys = array_flip($readStmt->fetchAll(\PDO::FETCH_COLUMN));

        $alerts = [];

        // Low stock alerts
        $lowStmt = $this->db->prepare('SELECT * FROM medicines WHERE stock_qty < ? ORDER BY stock_qty ASC');
        $lowStmt->execute([$threshold]);
        foreach ($lowStmt->fetchAll() as $m) {
            $key = 'low_' . $m['id'];
            $alerts[] = [
                'key' => $key,
                'type' => 'low_stock',
                'medicine_id' => (int)$m['id'],
                'brand_name' => $m['brand_name'] . ' ' . $m['strength'],
                'shelf_location' => $m['shelf_location'],
                'stock_qty' => (int)$m['stock_qty'],
                'message' => (int)$m['stock_qty'] === 0
                    ? "{$m['brand_name']} {$m['strength']} is out of stock."
                    : "{$m['brand_name']} {$m['strength']} is down to {$m['stock_qty']} units.",
                'read' => isset($readKeys[$key]),
            ];
        }

        // Expiry alerts
        $expStmt = $this->db->prepare("
            SELECT *, CAST((julianday(expiry_date) - julianday('now')) AS INTEGER) AS days_left
            FROM medicines
            WHERE expiry_date <= date('now', '+' || ? || ' days')
            ORDER BY expiry_date ASC
        ");
        $expStmt->execute([$expiryDays]);
        foreach ($expStmt->fetchAll() as $m) {
            $key = 'exp_' . $m['id'];
            $daysLeft = (int)$m['days_left'];
            $alerts[] = [
                'key' => $key,
                'type' => 'expiry',
                'medicine_id' => (int)$m['id'],
                'brand_name' => $m['brand_name'] . ' ' . $m['strength'],
                'shelf_location' => $m['shelf_location'],
                'days_left' => $daysLeft,
                'message' => "Batch {$m['batch_no']} of {$m['brand_name']} expires in {$daysLeft} days.",
                'read' => isset($readKeys[$key]),
            ];
        }

        return $alerts;
    }

    public function markAllRead(int $userId, array $keys): void
    {
        $stmt = $this->db->prepare('INSERT OR IGNORE INTO notification_reads (user_id, alert_key) VALUES (?, ?)');
        foreach ($keys as $key) {
            $stmt->execute([$userId, $key]);
        }
    }
}

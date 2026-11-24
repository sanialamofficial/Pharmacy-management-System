<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Medicine extends Model
{
    public const CATEGORIES = [
        'Allergy',
        'Analgesic',
        'Antibiotic',
        'Cardiovascular',
        'Dermatology',
        'Diabetes',
        'Eye & Ear',
        'Gastrointestinal',
        'Neurology & Mental Health',
        'Respiratory',
        'Vitamins & Supplements',
    ];

    public const FORMS = [
        'Tablet',
        'Capsule',
        'Syrup',
        'Suspension',
        'Inhaler',
        'Vial',
        'Cream',
        'Drops',
        'Sachet',
        'Ointment',
    ];

    public function getAll(array $filters = [], int $threshold = 5, int $expiryDays = 30): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['q'])) {
            $q = '%' . trim($filters['q']) . '%';
            $where[] = '(brand_name LIKE ? OR generic_name LIKE ? OR batch_no LIKE ? OR shelf_location LIKE ?)';
            array_push($params, $q, $q, $q, $q);
        }

        if (!empty($filters['category']) && $filters['category'] !== 'All') {
            $where[] = 'category = ?';
            $params[] = $filters['category'];
        }

        if (!empty($filters['in_stock_only'])) {
            $where[] = 'stock_qty > 0';
        }

        if (!empty($filters['quick'])) {
            switch ($filters['quick']) {
                case 'low':
                    $where[] = 'stock_qty < ?';
                    $params[] = $threshold;
                    break;
                case 'expiring':
                    $where[] = "expiry_date <= date('now', '+' || ? || ' days')";
                    $params[] = $expiryDays;
                    break;
                case 'rx':
                    $where[] = 'rx_required = 1';
                    break;
            }
        }

        $sql = 'SELECT *, CAST((julianday(expiry_date) - julianday("now")) AS INTEGER) AS days_to_expiry FROM medicines WHERE ' . implode(' AND ', $where) . ' ORDER BY stock_qty ASC, brand_name ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM medicines WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getSummary(int $threshold = 5): array
    {
        $total = (int)$this->db->query('SELECT COUNT(*) FROM medicines')->fetchColumn();
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM medicines WHERE stock_qty < ?');
        $stmt->execute([$threshold]);
        $low = (int)$stmt->fetchColumn();
        $value = (float)$this->db->query('SELECT COALESCE(SUM(unit_price * stock_qty), 0) FROM medicines')->fetchColumn();

        return [
            'total_products' => $total,
            'below_threshold' => $low,
            'retail_value' => round($value, 2),
        ];
    }

    public function getLowStock(int $threshold = 5, int $limit = 6): array
    {
        $stmt = $this->db->prepare('SELECT * FROM medicines WHERE stock_qty < ? ORDER BY stock_qty ASC, brand_name ASC LIMIT ?');
        $stmt->execute([$threshold, $limit]);
        return $stmt->fetchAll();
    }

    public function getExpiringSoon(int $days = 30, int $limit = 5): array
    {
        $stmt = $this->db->prepare("
            SELECT *, CAST((julianday(expiry_date) - julianday('now')) AS INTEGER) AS days_left
            FROM medicines
            WHERE expiry_date <= date('now', '+' || ? || ' days')
            ORDER BY expiry_date ASC
            LIMIT ?
        ");
        $stmt->execute([$days, $limit]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO medicines (brand_name, generic_name, strength, form, category, manufacturer, shelf_location, unit_price, stock_qty, batch_no, expiry_date, rx_required)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            trim($data['brand_name']),
            trim($data['generic_name']),
            trim($data['strength']),
            trim($data['form']),
            trim($data['category']),
            trim($data['manufacturer']),
            trim($data['shelf_location']),
            (float)$data['unit_price'],
            (int)$data['stock_qty'],
            trim($data['batch_no']),
            trim($data['expiry_date']),
            !empty($data['rx_required']) ? 1 : 0,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare('
            UPDATE medicines
            SET brand_name = ?, generic_name = ?, strength = ?, form = ?, category = ?, manufacturer = ?, shelf_location = ?, unit_price = ?, stock_qty = ?, batch_no = ?, expiry_date = ?, rx_required = ?
            WHERE id = ?
        ');
        return $stmt->execute([
            trim($data['brand_name']),
            trim($data['generic_name']),
            trim($data['strength']),
            trim($data['form']),
            trim($data['category']),
            trim($data['manufacturer']),
            trim($data['shelf_location']),
            (float)$data['unit_price'],
            (int)$data['stock_qty'],
            trim($data['batch_no']),
            trim($data['expiry_date']),
            !empty($data['rx_required']) ? 1 : 0,
            $id,
        ]);
    }

    public function restock(int $id, int $units): bool
    {
        if ($units <= 0) {
            return false;
        }
        $stmt = $this->db->prepare('UPDATE medicines SET stock_qty = stock_qty + ? WHERE id = ?');
        return $stmt->execute([$units, $id]);
    }

    public function delete(int $id): bool
    {
        $check = $this->db->prepare('SELECT COUNT(*) FROM sale_items WHERE medicine_id = ?');
        $check->execute([$id]);
        if ((int)$check->fetchColumn() > 0) {
            return false;
        }
        $stmt = $this->db->prepare('DELETE FROM medicines WHERE id = ?');
        return $stmt->execute([$id]);
    }
}

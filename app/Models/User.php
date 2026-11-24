<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE LOWER(email) = LOWER(?) LIMIT 1');
        $stmt->execute([trim($email)]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getStaffWithStats(): array
    {
        $sql = "
            SELECT
                u.*,
                COUNT(CASE WHEN s.created_at >= datetime('now', '-30 days') THEN 1 END) AS bills_30d,
                COALESCE(SUM(CASE WHEN s.created_at >= datetime('now', '-30 days') THEN s.total_amount ELSE 0 END), 0) AS revenue_30d,
                MAX(s.created_at) AS last_sale_at
            FROM users u
            LEFT JOIN sales s ON s.user_id = u.id
            WHERE u.role = 'staff'
            GROUP BY u.id
            ORDER BY u.status ASC, revenue_30d DESC, u.full_name ASC
        ";
        return $this->db->query($sql)->fetchAll();
    }

    public function getAllStaffForFilter(): array
    {
        return $this->db->query("SELECT id, full_name FROM users WHERE role = 'staff' ORDER BY full_name ASC")->fetchAll();
    }

    public function createStaff(array $data): int
    {
        $hash = password_hash($data['password'] ?? 'greenleaf', PASSWORD_DEFAULT);
        $stmt = $this->db->prepare(
            'INSERT INTO users (full_name, email, password_hash, role, job_title, shift, phone, status, joined_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            trim($data['full_name']),
            trim($data['email']),
            $hash,
            'staff',
            trim($data['job_title']),
            trim($data['shift']),
            trim($data['phone'] ?? ''),
            'active',
            date('Y-m-d'),
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function toggleStatus(int $id): bool
    {
        $user = $this->findById($id);
        if (!$user || $user['role'] === 'admin') {
            return false;
        }
        $newStatus = $user['status'] === 'active' ? 'suspended' : 'active';
        $stmt = $this->db->prepare('UPDATE users SET status = ? WHERE id = ?');
        return $stmt->execute([$newStatus, $id]);
    }

    public function resetPassword(int $id, string $newPassword): bool
    {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        return $stmt->execute([$hash, $id]);
    }

    public function deleteStaff(int $id): bool
    {
        $user = $this->findById($id);
        if (!$user || $user['role'] === 'admin') {
            return false;
        }
        $check = $this->db->prepare('SELECT COUNT(*) FROM sales WHERE user_id = ?');
        $check->execute([$id]);
        if ((int)$check->fetchColumn() > 0) {
            // Preserve ledger integrity: suspend if they have sales history
            $stmt = $this->db->prepare("UPDATE users SET status = 'suspended' WHERE id = ?");
            return $stmt->execute([$id]);
        }
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function updateProfile(int $id, array $data): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET full_name = ?, job_title = ?, email = ?, phone = ? WHERE id = ?');
        return $stmt->execute([
            trim($data['full_name']),
            trim($data['job_title']),
            trim($data['email']),
            trim($data['phone'] ?? ''),
            $id,
        ]);
    }

    public function updatePassword(int $id, string $newPassword): bool
    {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        return $stmt->execute([$hash, $id]);
    }
}

<?php
declare(strict_types=1);

class PolicyService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getAll(array $filters = []): array
    {
        $sql = "SELECT p.*, u.full_name AS creator_name
                FROM policies p
                JOIN users u ON u.id = p.created_by
                WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= ' AND p.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $sql .= ' AND (p.policy_number LIKE ? OR p.client_name LIKE ?)';
            $like = '%' . $filters['search'] . '%';
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= ' ORDER BY p.renewal_date ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return array_map(fn($r) => new Policy($r), $stmt->fetchAll());
    }

    public function getById(int $id): ?Policy
    {
        $stmt = $this->db->prepare(
            "SELECT p.*, u.full_name AS creator_name
             FROM policies p
             JOIN users u ON u.id = p.created_by
             WHERE p.id = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? new Policy($row) : null;
    }

    public function create(array $data, int $createdBy): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO policies
                (policy_number, client_name, insurance_type, premium_amount,
                 start_date, renewal_date, status, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['policy_number'],
            $data['client_name'],
            $data['insurance_type'],
            $data['premium_amount'],
            $data['start_date'],
            $data['renewal_date'],
            $data['status'],
            $createdBy,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE policies SET
                policy_number  = ?,
                client_name    = ?,
                insurance_type = ?,
                premium_amount = ?,
                start_date     = ?,
                renewal_date   = ?,
                status         = ?
             WHERE id = ?"
        );
        return $stmt->execute([
            $data['policy_number'],
            $data['client_name'],
            $data['insurance_type'],
            $data['premium_amount'],
            $data['start_date'],
            $data['renewal_date'],
            $data['status'],
            $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM policies WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function policyNumberExists(string $number, ?int $excludeId = null): bool
    {
        $sql    = 'SELECT id FROM policies WHERE policy_number = ?';
        $params = [$number];
        if ($excludeId !== null) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetch();
    }

    /** Dashboard summary counts */
    public function getSummary(): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                COUNT(*)                                                  AS total,
                SUM(status = 'active')                                    AS active,
                SUM(status = 'expired')                                   AS expired,
                SUM(status = 'pending_renewal')                           AS pending,
                SUM(
                    renewal_date >= CURDATE() AND
                    renewal_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
                )                                                         AS nearing
             FROM policies"
        );
        $stmt->execute([RENEWAL_NOTICE_DAYS]);
        return $stmt->fetch() ?: [];
    }

    public function getNearingRenewal(): array
    {
        $stmt = $this->db->prepare(
            "SELECT p.*, u.full_name AS creator_name
             FROM policies p
             JOIN users u ON u.id = p.created_by
             WHERE p.renewal_date >= CURDATE()
               AND p.renewal_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
             ORDER BY p.renewal_date ASC"
        );
        $stmt->execute([RENEWAL_NOTICE_DAYS]);
        return array_map(fn($r) => new Policy($r), $stmt->fetchAll());
    }

    /** Validate policy form data; returns array of error strings */
    public function validate(array $data, ?int $editId = null): array
    {
        $errors = [];

        if (empty(trim($data['policy_number'] ?? ''))) {
            $errors[] = 'Policy number is required.';
        } elseif ($this->policyNumberExists(trim($data['policy_number']), $editId)) {
            $errors[] = 'Policy number already exists.';
        }

        if (empty(trim($data['client_name'] ?? ''))) {
            $errors[] = 'Client name is required.';
        }

        if (empty(trim($data['insurance_type'] ?? ''))) {
            $errors[] = 'Insurance type is required.';
        }

        $premium = $data['premium_amount'] ?? '';
        if ($premium === '' || !is_numeric($premium) || (float)$premium < 0) {
            $errors[] = 'A valid premium amount is required.';
        }

        if (empty($data['start_date'])) {
            $errors[] = 'Start date is required.';
        }

        if (empty($data['renewal_date'])) {
            $errors[] = 'Renewal date is required.';
        }

        if (!empty($data['start_date']) && !empty($data['renewal_date'])
            && $data['renewal_date'] <= $data['start_date']) {
            $errors[] = 'Renewal date must be after start date.';
        }

        $validStatuses = ['active', 'expired', 'pending_renewal'];
        if (!in_array($data['status'] ?? '', $validStatuses, true)) {
            $errors[] = 'Invalid status selected.';
        }

        return $errors;
    }
}

<?php
declare(strict_types=1);

class UserService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getAll(): array
    {
        $stmt = $this->db->query(
            'SELECT * FROM users ORDER BY created_at DESC'
        );
        return array_map(fn($r) => new User($r), $stmt->fetchAll());
    }

    public function getById(int $id): ?User
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? new User($row) : null;
    }

    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $sql    = 'SELECT id FROM users WHERE email = :e';
        $params = [':e' => strtolower($email)];
        if ($excludeId !== null) {
            $sql .= ' AND id != :id';
            $params[':id'] = $excludeId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetch();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO users (full_name, email, password, role, is_active)
             VALUES (:fn, :em, :pw, :rl, 1)"
        );
        $stmt->execute([
            ':fn' => trim($data['full_name']),
            ':em' => strtolower(trim($data['email'])),
            ':pw' => AuthService::hashPassword($data['password']),
            ':rl' => $data['role'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql    = 'UPDATE users SET full_name = :fn, email = :em, role = :rl, is_active = :ia';
        $params = [
            ':fn' => trim($data['full_name']),
            ':em' => strtolower(trim($data['email'])),
            ':rl' => $data['role'],
            ':ia' => (int) ($data['is_active'] ?? 1),
            ':id' => $id,
        ];

        if (!empty($data['password'])) {
            $sql .= ', password = :pw';
            $params[':pw'] = AuthService::hashPassword($data['password']);
        }

        $sql .= ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function toggleActive(int $id, bool $active): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET is_active = :ia WHERE id = :id');
        return $stmt->execute([':ia' => (int) $active, ':id' => $id]);
    }

    public function validate(array $data, bool $isNew, ?int $editId = null): array
    {
        $errors = [];

        if (empty(trim($data['full_name'] ?? ''))) {
            $errors[] = 'Full name is required.';
        }

        $email = trim($data['email'] ?? '');
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid email address is required.';
        } elseif ($this->emailExists($email, $editId)) {
            $errors[] = 'Email address is already in use.';
        }

        if ($isNew) {
            $pw = $data['password'] ?? '';
            if (strlen($pw) < 8) {
                $errors[] = 'Password must be at least 8 characters.';
            }
        }

        $validRoles = ['admin', 'policy_officer', 'viewer'];
        if (!in_array($data['role'] ?? '', $validRoles, true)) {
            $errors[] = 'Invalid role selected.';
        }

        return $errors;
    }
}

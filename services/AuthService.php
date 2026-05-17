<?php
declare(strict_types=1);

class AuthService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function login(string $email, string $password): ?User
    {
        $email = trim(strtolower($email));

        $stmt = $this->db->prepare(
            'SELECT * FROM users WHERE email = :email AND is_active = 1 LIMIT 1'
        );
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();

        // PHP uses $2y$, Python bcrypt uses $2b$ — normalise so both work
        $hash = str_replace('$2b$', '$2y$', $row['password'] ?? '');

        if ($row && password_verify($password, $hash)) {
            $user = new User($row);
            $this->startSession($user);
            return $user;
        }

        return null;
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    private function startSession(User $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id']    = $user->id;
        $_SESSION['user_role']  = $user->role;
        $_SESSION['user_name']  = $user->fullName;
        $_SESSION['last_active'] = time();
    }

    public function getCurrentUser(): ?User
    {
        if (empty($_SESSION['user_id'])) {
            return null;
        }

        // Session timeout check
        if (isset($_SESSION['last_active']) &&
            (time() - $_SESSION['last_active']) > SESSION_TIMEOUT) {
            $this->logout();
            return null;
        }

        $_SESSION['last_active'] = time();

        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id AND is_active = 1 LIMIT 1');
        $stmt->execute([':id' => $_SESSION['user_id']]);
        $row = $stmt->fetch();

        return $row ? new User($row) : null;
    }

    public function requireLogin(): User
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            header('Location: ' . APP_URL . '/index.php?page=login');
            exit;
        }
        return $user;
    }

    public function requireRole(User $user, array $roles): void
    {
        if (!in_array($user->role, $roles, true)) {
            header('Location: ' . APP_URL . '/index.php?page=dashboard&error=forbidden');
            exit;
        }
    }

    public static function hashPassword(string $plain): string
    {
        return password_hash($plain, PASSWORD_BCRYPT, ['cost' => 12]);
    }
}

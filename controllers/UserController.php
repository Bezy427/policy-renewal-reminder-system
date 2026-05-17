<?php
declare(strict_types=1);

class UserController
{
    private UserService    $userService;
    private AuthService    $authService;
    private ActivityLogger $logger;

    public function __construct(
        UserService    $userService,
        AuthService    $authService,
        ActivityLogger $logger
    ) {
        $this->userService = $userService;
        $this->authService = $authService;
        $this->logger      = $logger;
    }

    public function handle(string $action, User $currentUser): void
    {
        $this->authService->requireRole($currentUser, ['admin']);

        match ($action) {
            'create' => $this->create($currentUser),
            'edit'   => $this->edit($currentUser),
            'toggle' => $this->toggle($currentUser),
            default  => $this->index($currentUser),
        };
    }

    private function index(User $currentUser): void
    {
        $users = $this->userService->getAll();
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        require VIEW_DIR . '/users/index.php';
    }

    private function create(User $currentUser): void
    {
        $this->verifyCsrf();
        $data   = $this->sanitize($_POST);
        $errors = $this->userService->validate($data, true);

        if (empty($errors)) {
            $id = $this->userService->create($data);
            $this->logger->log($currentUser->id, 'user_create', 'user', $id, $data['email']);
            $this->flash('User created successfully.', 'success');
            $this->redirect('users');
        } else {
            // Re-render the users page with errors
            $users        = $this->userService->getAll();
            $createErrors = $errors;
            $createData   = $data;
            $flash        = null;
            require VIEW_DIR . '/users/index.php';
        }
    }

    private function edit(User $currentUser): void
    {
        $this->verifyCsrf();
        $id   = (int) ($_POST['id'] ?? 0);
        $data = $this->sanitize($_POST);
        $data['is_active'] = $_POST['is_active'] ?? '1';
        $errors = $this->userService->validate($data, false, $id);

        if (empty($errors)) {
            $this->userService->update($id, $data);
            $this->logger->log($currentUser->id, 'user_update', 'user', $id, $data['email']);
            $this->flash('User updated.', 'success');
            $this->redirect('users');
        } else {
            $users      = $this->userService->getAll();
            $editErrors = $errors;
            $editData   = array_merge($data, ['id' => $id]);
            $flash      = null;
            require VIEW_DIR . '/users/index.php';
        }
    }

    private function toggle(User $currentUser): void
    {
        $id   = (int) ($_GET['id'] ?? 0);
        $user = $this->userService->getById($id);

        if ($user && $user->id !== $currentUser->id) {
            $newActive = !$user->isActive;
            $this->userService->toggleActive($id, $newActive);
            $this->logger->log($currentUser->id, $newActive ? 'user_activate' : 'user_deactivate', 'user', $id);
            $this->flash('User ' . ($newActive ? 'activated' : 'deactivated') . '.', 'success');
        } else {
            $this->flash('Cannot deactivate your own account.', 'error');
        }
        $this->redirect('users');
    }

    private function sanitize(array $post): array
    {
        return [
            'full_name' => trim($post['full_name'] ?? ''),
            'email'     => trim($post['email']     ?? ''),
            'password'  => $post['password']        ?? '',
            'role'      => trim($post['role']       ?? ''),
        ];
    }

    private function flash(string $msg, string $type): void
    {
        $_SESSION['flash'] = ['message' => $msg, 'type' => $type];
    }

    private function redirect(string $page): void
    {
        header('Location: ' . APP_URL . '/index.php?page=' . $page);
        exit;
    }

    private function verifyCsrf(): void
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            die('Invalid CSRF token.');
        }
    }
}

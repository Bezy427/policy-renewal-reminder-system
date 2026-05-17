<?php
declare(strict_types=1);

class PolicyController
{
    private PolicyService  $policyService;
    private DocumentService $docService;
    private AuthService    $authService;
    private ActivityLogger $logger;

    public function __construct(
        PolicyService   $policyService,
        DocumentService $docService,
        AuthService     $authService,
        ActivityLogger  $logger
    ) {
        $this->policyService = $policyService;
        $this->docService    = $docService;
        $this->authService   = $authService;
        $this->logger        = $logger;
    }

    public function handle(string $action, User $currentUser): void
    {
        match ($action) {
            'create' => $this->create($currentUser),
            'edit'   => $this->edit($currentUser),
            'delete' => $this->delete($currentUser),
            'view'   => $this->view($currentUser),
            default  => $this->index($currentUser),
        };
    }

    private function index(User $currentUser): void
    {
        $filters  = [
            'status' => $_GET['status'] ?? '',
            'search' => $_GET['search'] ?? '',
        ];
        $policies = $this->policyService->getAll($filters);
        $flash    = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $pageTitle  = 'Policies';
        $activePage = 'policies';
        require VIEW_DIR . '/policies/list.php';
    }

    private function view(User $currentUser): void
    {
        $id     = (int) ($_GET['id'] ?? 0);
        $policy = $this->policyService->getById($id);
        if (!$policy) { $this->redirect('policies', 'Policy not found.', 'error'); return; }

        $documents = $this->docService->getByPolicy($id);
        $flash     = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        require VIEW_DIR . '/policies/view.php';
    }

    private function create(User $currentUser): void
    {
        $this->authService->requireRole($currentUser, ['admin', 'policy_officer']);

        $errors   = [];
        $formData = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();
            $formData = $this->sanitizeForm($_POST);
            $errors   = $this->policyService->validate($formData);

            if (empty($errors)) {
                $id = $this->policyService->create($formData, $currentUser->id);
                $this->logger->log($currentUser->id, 'policy_create', 'policy', $id, $formData['policy_number']);
                $this->redirect('policies', 'Policy created successfully.', 'success');
                return;
            }
        }

        $pageTitle  = 'Add Policy';
        $activePage = 'policies_add';
        $policy     = null;
        require VIEW_DIR . '/policies/form.php';
    }

    private function edit(User $currentUser): void
    {
        $this->authService->requireRole($currentUser, ['admin', 'policy_officer']);

        $id     = (int) ($_GET['id'] ?? 0);
        $policy = $this->policyService->getById($id);
        if (!$policy) { $this->redirect('policies', 'Policy not found.', 'error'); return; }

        $errors   = [];
        $formData = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();
            $formData = $this->sanitizeForm($_POST);
            $errors   = $this->policyService->validate($formData, $id);

            if (empty($errors)) {
                $this->policyService->update($id, $formData);
                $this->logger->log($currentUser->id, 'policy_update', 'policy', $id, $formData['policy_number']);
                $this->redirect('policies', 'Policy updated successfully.', 'success');
                return;
            }
        }

        $pageTitle  = 'Edit Policy';
        $activePage = 'policies';
        require VIEW_DIR . '/policies/form.php';
    }

    private function delete(User $currentUser): void
    {
        $this->authService->requireRole($currentUser, ['admin', 'policy_officer']);
        $id     = (int) ($_GET['id'] ?? 0);
        $policy = $this->policyService->getById($id);

        if ($policy) {
            $this->policyService->delete($id);
            $this->logger->log($currentUser->id, 'policy_delete', 'policy', $id, $policy->policyNumber);
            $this->redirect('policies', 'Policy deleted.', 'success');
        } else {
            $this->redirect('policies', 'Policy not found.', 'error');
        }
    }

    private function sanitizeForm(array $post): array
    {
        return [
            'policy_number'  => trim($post['policy_number']  ?? ''),
            'client_name'    => trim($post['client_name']    ?? ''),
            'insurance_type' => trim($post['insurance_type'] ?? ''),
            'premium_amount' => trim($post['premium_amount'] ?? ''),
            'start_date'     => trim($post['start_date']     ?? ''),
            'renewal_date'   => trim($post['renewal_date']   ?? ''),
            'status'         => trim($post['status']         ?? ''),
        ];
    }

    private function redirect(string $page, string $msg, string $type): void
    {
        $_SESSION['flash'] = ['message' => $msg, 'type' => $type];
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

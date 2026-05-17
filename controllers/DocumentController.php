<?php
declare(strict_types=1);
class DocumentController
{
    private DocumentService $docService;
    private AuthService     $authService;
    private ActivityLogger  $logger;

    public function __construct(
        DocumentService $docService,
        AuthService     $authService,
        ActivityLogger  $logger
    ) {
        $this->docService  = $docService;
        $this->authService = $authService;
        $this->logger      = $logger;
    }

    public function handle(string $action, User $currentUser): void
    {
        match ($action) {
            'upload'   => $this->upload($currentUser),
            'download' => $this->download($currentUser),
            'delete'   => $this->delete($currentUser),
            default    => $this->redirect('policies'),
        };
    }

    private function upload(User $currentUser): void
    {
        $this->authService->requireRole($currentUser, ['admin', 'policy_officer']);

        $this->verifyCsrf();
        $policyId = (int) ($_POST['policy_id'] ?? 0);

        if (!isset($_FILES['document'])) {
            $this->redirectToPolicy($policyId, 'No file was uploaded.', 'error');
            return;
        }

        [$success, $message] = $this->docService->upload(
            $_FILES['document'], $policyId, $currentUser->id
        );

        if ($success) {
            $this->logger->log($currentUser->id, 'document_upload', 'policy', $policyId, $_FILES['document']['name']);
        }

        $this->redirectToPolicy($policyId, $message, $success ? 'success' : 'error');
    }

    private function download(User $currentUser): void
    {
        $id  = (int) ($_GET['id'] ?? 0);
        $doc = $this->docService->getById($id);

        if (!$doc || !file_exists($doc->getStoredPath())) {
            http_response_code(404);
            die('File not found.');
        }

        $this->logger->log($currentUser->id, 'document_download', 'document', $id, $doc->fileName);

        header('Content-Type: ' . $doc->fileType);
        header('Content-Disposition: attachment; filename="' . addslashes($doc->fileName) . '"');
        header('Content-Length: ' . filesize($doc->getStoredPath()));
        header('X-Content-Type-Options: nosniff');
        readfile($doc->getStoredPath());
        exit;
    }

    private function delete(User $currentUser): void
    {
        $this->authService->requireRole($currentUser, ['admin', 'policy_officer']);
        $id       = (int) ($_GET['id']        ?? 0);
        $policyId = (int) ($_GET['policy_id'] ?? 0);

        $doc = $this->docService->getById($id);
        if ($doc) {
            $this->docService->delete($id);
            $this->logger->log($currentUser->id, 'document_delete', 'document', $id, $doc->fileName);
            $this->redirectToPolicy($policyId, 'Document deleted.', 'success');
        } else {
            $this->redirectToPolicy($policyId, 'Document not found.', 'error');
        }
    }

    private function redirectToPolicy(int $policyId, string $msg, string $type): void
    {
        $_SESSION['flash'] = ['message' => $msg, 'type' => $type];
        header('Location: ' . APP_URL . '/index.php?page=policies&action=view&id=' . $policyId);
        exit;
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

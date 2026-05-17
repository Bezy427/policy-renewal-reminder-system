<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);


require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/Database.php';

require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Policy.php';
require_once __DIR__ . '/models/Document.php';

require_once __DIR__ . '/services/AuthService.php';
require_once __DIR__ . '/services/PolicyService.php';
require_once __DIR__ . '/services/DocumentService.php';
require_once __DIR__ . '/services/UserService.php';
require_once __DIR__ . '/services/ActivityLogger.php';

require_once __DIR__ . '/controllers/PolicyController.php';
require_once __DIR__ . '/controllers/DocumentController.php';
require_once __DIR__ . '/controllers/UserController.php';

define('VIEW_DIR', __DIR__ . '/views');

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$db             = Database::getInstance();
$auth           = new AuthService($db);
$logger         = new ActivityLogger($db);
$policyService  = new PolicyService($db);
$docService     = new DocumentService($db);
$userService    = new UserService($db);

$policyCtrl = new PolicyController($policyService, $docService, $auth, $logger);
$docCtrl    = new DocumentController($docService, $auth, $logger);
$userCtrl   = new UserController($userService, $auth, $logger);

$page   = preg_replace('/[^a-z_]/', '', strtolower($_GET['page']   ?? 'dashboard'));
$action = preg_replace('/[^a-z_]/', '', strtolower($_GET['action'] ?? 'index'));

if ($page === 'login') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');

        $token = $_POST['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            $error = 'Security token mismatch. Please try again.';
        } else {
            $user = $auth->login($email, $password);
            if ($user) {
                $logger->log($user->id, 'login');
                header('Location: ' . APP_URL . '/index.php?page=dashboard');
                exit;
            } else {
                $error = 'Invalid email or password.';
            }
        }
    }
    $currentUser = null;
    require VIEW_DIR . '/auth/login.php';
    exit;
}

if ($page === 'logout') {
    $uid = $_SESSION['user_id'] ?? null;
    $auth->logout();
    if ($uid) $logger->log($uid, 'logout');
    header('Location: ' . APP_URL . '/index.php?page=login');
    exit;
}

$currentUser = $auth->requireLogin();

switch ($page) {
    case 'dashboard':
        $summary         = $policyService->getSummary();
        $nearingPolicies = $policyService->getNearingRenewal();
        $pageTitle       = 'Dashboard';
        $activePage      = 'dashboard';
        require VIEW_DIR . '/dashboard/index.php';
        break;

    case 'policies':
        $policyCtrl->handle($action, $currentUser);
        break;

    case 'documents':
        $docCtrl->handle($action, $currentUser);
        break;

    case 'users':
        $userCtrl->handle($action, $currentUser);
        break;

    default:
        header('Location: ' . APP_URL . '/index.php?page=dashboard');
        exit;
}

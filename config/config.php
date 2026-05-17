<?php
declare(strict_types=1);

define('DB_HOST',    getenv('DB_HOST')    ?: 'localhost');
define('DB_PORT',    getenv('DB_PORT')    ?: '3306');
define('DB_NAME',    getenv('DB_NAME')    ?: 'policy_renewal_db');
define('DB_USER',    getenv('DB_USER')    ?: '');
define('DB_PASS',    getenv('DB_PASS')    ?: '');
define('DB_CHARSET', 'utf8mb4');


if (!defined('APP_URL')) {
    if (getenv('APP_URL')) {
        define('APP_URL', rtrim(getenv('APP_URL'), '/'));
    } else {
        $scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
        // Derive the sub-directory path from __FILE__ relative to the document root
        $docRoot  = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\');
        $selfDir  = rtrim(dirname(dirname(__FILE__)), '/\\'); // one level up from /config
        $subPath  = str_replace('\\', '/', substr($selfDir, strlen($docRoot)));
        define('APP_URL', rtrim($scheme . '://' . $host . $subPath, '/'));
    }
}
define('APP_NAME', 'PolicyRenew');

define('UPLOAD_DIR',      __DIR__ . '/../uploads/');
define('UPLOAD_MAX_MB',   5);
define('ALLOWED_TYPES',   ['image/jpeg', 'image/png', 'application/pdf']);
define('ALLOWED_EXTS',    ['jpg', 'jpeg', 'png', 'pdf']);

define('SESSION_TIMEOUT', 1800);

define('RENEWAL_NOTICE_DAYS', 30);

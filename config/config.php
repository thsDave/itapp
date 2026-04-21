<?php
declare(strict_types=1);

define('APP_NAME',  'IT App');
define('APP_URL',   'http://localhost/itapp/public');
define('APP_ENV',   'development');
define('APP_DEBUG', APP_ENV === 'development');

date_default_timezone_set('America/El_Salvador');

if (APP_DEBUG) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('log_errors',     '1');
    ini_set('error_log',      BASE_PATH . '/storage/logs/app.log');
}

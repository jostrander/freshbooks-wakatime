#!/usr/bin/env php
<?php
/*
 * Load Composer's Autoload File
 */

require __DIR__ . '/vendor/autoload.php';

use App\Boot\Bootstrap;
use Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(__DIR__);
$dotenv->required([
        'FRESHBOOKS_API_KEY',
        'FRESHBOOKS_TASK_ID',
        'WAKATIME_API_KEY',
        'MANDRILL.API_KEY',
        'MANDRILL.FROM_EMAIL',
        'MANDRILL.FROM_NAME',
        'MANDRILL.TO_EMAIL'
    ]
)->notEmpty();

if (getenv('TIMEZONE')) {
    date_default_timezone_set(getenv('TIMEZONE'));
}

$options = getopt("d::");
if (isset($options['d'])) {
    $runAsDate = new DateTime($options['d']);
} else {
    $runAsDate = new DateTime();
}
/*
 * Start Application
 */
Bootstrap::start($runAsDate);
<?php
/**
 * Created by PhpStorm.
 * User: jesse
 * Date: 18/03/15
 * Time: 1:43 AM
 */

/*
 * Load Composer's Autoload File
 */

require __DIR__ . '/vendor/autoload.php';

use App\Boot\Bootstrap;
use Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(__DIR__);
$dotenv->required(array('FRESHBOOKS_API_KEY', 'WAKATIME_API_KEY', 'FRESHBOOKS_TASK_ID'))->notEmpty();
/*
 * Start Application
 */
Bootstrap::start();
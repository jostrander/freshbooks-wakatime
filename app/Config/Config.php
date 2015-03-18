<?php
/**
 * Created by PhpStorm.
 * User: jesse
 * Date: 18/03/15
 * Time: 1:48 AM
 */

namespace App\Config;


class Config {

    public static function get_wakatime_key() {
        return getenv('WAKATIME_API_KEY');
    }
    public static function get_freshbooks_key() {
        return getenv('FRESHBOOKS_API_KEY');
    }
    public static function get_freshbooks_task() {
        return getenv('FRESHBOOKS_TASK_ID');
    }
    public static function get_freshbooks_domain() {
        return getenv('FRESHBOOKS_SUB_DOMAIN');
    }
}
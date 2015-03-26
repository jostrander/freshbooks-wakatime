<?php
/**
 * Created by PhpStorm.
 * User: jesse
 * Date: 18/03/15
 * Time: 1:48 AM
 */

namespace App\Config;


class Config {

    public static function get($key) {
        return getenv($key);
    }
}
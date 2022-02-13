<?php

/**
 * Plugin Name: Newt
 * Plugin URI:  https://infonique.ch/newt
 * Description: Plugin for Worpress to manage Newt-Endpoints
 * Version:     1.0.0
 * Author:      Juergen Furrer
 * Author URI:  https://infonique.ch
 */

require "vendor/autoload.php";

use Infonique\Newt\Backend\Settings;

$settings = new Settings();
$settings->admin_menu();

/*
$debug_tags = [];
add_action('all', function ($tag) {
    global $debug_tags;
    if (in_array($tag, $debug_tags)) {
        return;
    }
    $debug_tags[] = $tag;
    echo "<pre style=\"color:red\">{$tag}</pre>";
});
*/

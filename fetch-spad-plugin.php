<?php
/*
Plugin Name: Fetch SPAD
Plugin URI: https://wordpress.org/plugins/fetch-spad/
Description: This is a plugin that fetches A Spiritual Principle A Day and puts it on your site Simply add [spad] shortcode to your page. Fetch SPAD Widget can be added to your sidebar or footer as well.
Version: 1.1.0
Install: Drop this directory into the "wp-content/plugins/" directory and activate it.
*/
/* Disallow direct access to the plugin file */
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Sorry, but you cannot access this page directly.');
}

spl_autoload_register(function (string $class) {
    if (strpos($class, 'Spad\\') === 0) {
        $class = str_replace('Spad\\', '', $class);
        require __DIR__ . '/src/' . str_replace('\\', '/', $class) . '.php';
    }
});

use Spad\Dashboard;
use Spad\Reading;
use Spad\Widget;

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

class FetchSPADPlugin
{
    // phpcs:enable PSR1.Classes.ClassDeclaration.MissingNamespace
    public function __construct()
    {
        add_action('admin_menu', [$this, 'optionsMenu']);
        add_action('wp_enqueue_scripts', [$this, 'assets']);
        add_shortcode('spad', [$this, 'reading']);
        add_action('widgets_init', function () {
            register_widget(Widget::class);
        });
    }

    public function optionsMenu()
    {
        $dashboard = new Dashboard();
        $dashboard->createMenu();
    }

    public function reading($atts)
    {
        $reading = new Reading();
        return $reading->renderReading($atts);
    }

    public function assets()
    {
        wp_enqueue_style("spadcss", plugin_dir_url(__FILE__) . "css/spad.css", false, filemtime(plugin_dir_path(__FILE__) . "css/spad.css"), false);
    }
}

$fetchSPADPlugin = new FetchSPADPlugin();

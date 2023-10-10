<?php
/*
Plugin Name: Contact Form Tutorial
Plugin URI: https://contactform7.com/
Description: Just another contact form plugin. Simple but flexible.
Author: Takayuki Miyoshi
Author URI: https://ideasilo.wordpress.com/
Text Domain: contact-plugin
Domain Path: /languages/
Version: 5.7.7
*/

if (!class_exists('ContactPlugin')) {
    class ContactPlugin{

        public function __construct(){
            //define plugin path so taht it can be reused
            define('MY_PLUGIN_PATH', plugin_dir_path(__FILE__));

            //require plugin path
            require_once( MY_PLUGIN_PATH . '/vendor/autoload.php');
        }

        public function initialize(){
            include_once MY_PLUGIN_PATH . 'includes/utilities.php';
            include_once MY_PLUGIN_PATH . 'includes/options-page.php';
            include_once MY_PLUGIN_PATH . 'includes/contact-form.php';
        }
    }
    $contactPlugin = new ContactPlugin;
    $contactPlugin->initialize();
}

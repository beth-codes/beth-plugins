<?php
//enque style sheet
function register_styles() {
   wp_enqueue_style( 'my_theme_style', get_stylesheet_uri(), array(), '', 'all' );
}
add_action( 'wp_enqueue_scripts', 'register_styles' );



//register menu
function register_menus() { 
   register_nav_menu('main-menu',__('Main Menu')); 
} 
add_action('init', 'register_menus');
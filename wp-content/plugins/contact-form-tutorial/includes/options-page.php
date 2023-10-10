<?php
//load carbon fields
use Carbon_Fields\Container;
use Carbon_Fields\Field;

add_action('after_setup_theme', 'load_carbon_fields');
add_action('carbon_fields_register_fields', 'create_options_page');

function load_carbon_fields(){
    \Carbon_Fields\Carbon_Fields::boot();
}

function create_options_page(){
    
    Container::make( 'theme_options', 'Contact Form' )
    ->set_page_menu_position(30)->add_fields( array(
        Field::make( 'checkbox', 'contact_plugin_active', __('Is Active')),

        Field::make( 'text', 'contact_plugin_recipients' , __('Recipient Email'))->set_attribute( 'placeholder', 'Email' )->set_help_text( 'The email that the form is submitted to.',) ,

        Field::make( 'textarea', 'contact_plugin_meassage' , __('Confirmation Message'))->set_attribute( 'placeholder', 'Message' )->set_help_text( 'Type the message you want to send to recipient.',) 
    ) );
}
<?php
//add action for shortcode: shortcode and shortcode function
add_shortcode('contact', 'show_contact_form');

//add action for custom rest api
add_action('rest_api_init', 'create_rest_endpoint');
//shortcode function

//add action for submission page
add_action('init', 'create_submission_page');

//add meta box to display form submission
add_action('add_meta_boxes', 'create_meta_box');

add_filter('manage_submission_posts_columns', 'custom_submission_columns');

add_action('manage_submission_posts_custom_column', 'fill_ubmission_columns', 10, 2);

add_action('admin_init', 'setup_search');

function setup_search(){
    global $typenow;

    if (!$typenow === 'submission') {
        add_filter('posts_search', 'submission_search_override', 10, 2);
    }
}
//PROBABLY REMOVE THIS SEARCH EMAIL AND PHONE DOESN'T WORK
function submission_search_override($search, $query)
{
      // Override the submissions page search to include custom meta data

      global $wpdb;

      if ($query->is_main_query() && !empty($query->query['s'])) {
            $sql    = "
              or exists (
                  select * from {$wpdb->postmeta} where post_id={$wpdb->posts}.ID
                  and meta_key in ('name','email')
                  and meta_value like %s
              )
          ";
            $like   = '%' . $wpdb->esc_like($query->query['s']) . '%';
            $search = preg_replace(
                  "#\({$wpdb->posts}.post_title LIKE [^)]+\)\K#",
                  $wpdb->prepare($sql, $like),
                  $search
            );
      }

      return $search;
}

function fill_ubmission_columns($column, $post_id){
    switch($column){
        case 'name':
            echo esc_html(get_post_meta($post_id, 'name', true));
        break;
        case 'email':
            echo esc_html(get_post_meta($post_id, 'email', true));
        break;
        case 'phone':
            echo esc_html(get_post_meta($post_id, 'tel', true));
        break;
        case 'message':
            echo esc_html(get_post_meta($post_id, 'message', true));
        break;
    }
}

function custom_submission_columns ($columns){

    $columns = [
        'cb' => $columns['cb'],
        'name' => __('Name', 'contact-plugin'),
        'email' => __('Email', 'contact-plugin'),
        'phone' => __('Phone', 'contact-plugin'),
        'message' => __('Message', 'contact-plugin'),
    ];

    return $columns;
}

function create_meta_box(){
    add_meta_box('custom_contact_form', 'Submission', 'display_submission', 'submission');
}

function display_submission(){
   $post_metas = get_post_meta(get_the_ID());
   //remove data we dont want to pass
   unset($post_metas['_edit_lock']);
   unset($post_metas[ '_edit_last']);
    echo '<ul>';
   foreach($post_metas as $key => $value){
    echo '<li> <strong>' . ucfirst($key) . '</strong>:<br> ' . $value[0]. '</li>';
   }

   echo '</ul>';
}


function create_submission_page(){
    $args = [
        'public' => true,
        'has_archive' => true,
        'menu_position' => 30,
        'publicly_queryable' => false,
        'labels' => [
            'name' => 'Submissions',
            'singular_name' => 'Submission',
            'edit_item' => 'View Submission',
        ],
        'supports' => false,
        'capability_type' => 'post',
        'capabilities'=> [
            'create_posts' => false,
        ],
        'map_meta_cap'=> true,
        ];

    register_post_type('submission', $args);
}

function show_contact_form(){
    //INCLUDE CONTACT FROM TEMPLATE
    include MY_PLUGIN_PATH . '/includes/templates/contact-form.php';

    
}

//CREATE CUSTOM REST API ENDPOINT
function create_rest_endpoint(){
    register_rest_route('v1/contact-form', 'submit', array(
        'methods' => 'POST',
        'callback' => 'handle_enquiry'
    ));
}

function handle_enquiry($data){
    $params = $data->get_params();
//check if wp nonce is active, because it was added on the form for security
   if (!wp_verify_nonce($params['_wpnonce'], 'wp_rest')) {
    return new WP_Rest_Response('Message not sent', 422);
   }


//remove wp nonce
   unset($params['_wpnonce']);
   unset($params['_wp_http_referer']);

   //send the email message
   $headers = [];
   $admin_name = get_bloginfo('name');
   $admin_email = get_bloginfo('admin_email');

   //set recipient email
   $recipient_email = get_plugin_options('contact_plugin_recipients'); 

   if(!$recipient_email){
    $recipient_email = $admin_email;
   }

   $subject = "New Enquiry from {$params['name']}";

   $headers[] = "From: {$admin_name} <{$admin_email}>";
   $headers[] = "Reply-to: {$params['name']} <{$params['email']}>";
   $headers[] = "Content-Type: text/html";

   $message = '';
   $message .= "Message has been sent from {$params['name']}.";

   $postarr = [
       'post_title' => $params['name'],
       'post_type' => 'submission',
       'post_status' => 'publish',
   ];

   $post_id = wp_insert_post($postarr);

   foreach ($params as $label => $value) {
    $message .= ucfirst($label). ':' . $value;
    //sanitize text field value for security
    add_post_meta($post_id, sanitize_text_field($label), sanitize_text_field($value));
   }


   wp_mail($recipient_email, $subject, $message, $headers);

   //set confirmation message
   $confirmation_message = 'This Message was sent sucessfully!';

   if (get_plugin_options('contact_plugin_meassage')) {
    $confirmation_message = get_plugin_options('contact_plugin_meassage');$confirmation_message = str_replace('name', $params['name'], $confirmation_message);
   }

   //return successful response
   return new WP_Rest_Response($confirmation_message, 200);
}


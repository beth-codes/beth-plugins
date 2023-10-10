<?php


// Add a custom menu page
add_action('admin_menu', 'my_plugin_add_menu_page');

function my_plugin_add_menu_page()
{
   add_menu_page(
      'Tagged Posts',
      // Page title
      'Tags',
      // Menu title
      'manage_options',
      // Capability required to access the page
      'my-plugin-tagged-posts',
      // Slug
      'my_plugin_render_tagged_posts_page',
      // Callback function to render the page
      'dashicons-tag' // Menu icon
   );
}

add_action('admin_post_my_plugin_tagged_posts_filter', 'my_plugin_handle_tagged_posts_filter');
add_action('admin_post_nopriv_my_plugin_tagged_posts_filter', 'my_plugin_handle_tagged_posts_filter');

// Callback function to render the "Tags" page
function my_plugin_render_tagged_posts_page()
{
   // Get the selected post types from plugin settings
   $selected_post_types = get_option('my_plugin_post_types');

   // Get the searched tag value
   $search_tag = isset($_GET['search_tag']) ? sanitize_text_field($_GET['search_tag']) : '';

   // Get the selected post type filter value
   $post_type_filter = isset($_GET['post_type_filter']) ? sanitize_text_field($_GET['post_type_filter']) : '';

   // Get the selected sort by parameter STEP 6
   $sort_by = isset($_GET['sort_by']) ? sanitize_text_field($_GET['sort_by']) : '';

   // Query arguments
   $args = array(
      'post_type' => $selected_post_types,
      // Filter by selected post types
      'meta_query' => array(
         array(
            'key' => 'my_plugin_tag_reference',
            'compare' => 'EXISTS',
            // Only fetch posts with a tag reference
         ),
      ),
      'orderby' => 'title',
      // Default sorting by post title
      'order' => 'ASC',
      // Default sort order
   );

   // Add tag query if a search tag is specified
   if (!empty($search_tag)) {
      $args['search_tag'] = $search_tag;

   }
   // Add post type filter if specified
   if (!empty($post_type_filter)) {
      $args['post_type'] = $post_type_filter;
   }

   if (!empty($sort_by)) {
      switch ($sort_by) {
         case 'id':
            $args['orderby'] = 'id';
            break;
         case 'title':
            $args['orderby'] = 'title';
            break;
         case 'tag_reference':
            $args['orderby'] = 'meta_value';
            $args['meta_key'] = 'my_plugin_tag_reference';
            break;
         case 'post_type':
            $args['orderby'] = 'post_type';
            break;
      }
   }
   // Add sorting parameters if specified

   // Fetch tagged posts
   $tagged_posts = new WP_Query($args);

   ?>

   <div class="wrap">
      <h1>Tagged Posts</h1>
      <script>
         jQuery(document).ready(function ($) {
            $('#tagged_posts_filter').on('submit', function (event) {
               event.preventDefault(); // Prevent default form submission

               var form = $(this);

               // Construct new URL based on form data
               var newUrl = form.attr('action') + '?' + form.serialize();

               // Update the URL without redirecting using history.replaceState()
               history.replaceState(null, null, newUrl);

               // Trigger a custom event to indicate filtering
               $(document).trigger('tagged_posts_filter');

               // Trigger a page reload to reflect the filtered result
               location.reload();
            });
         });

      </script>

      <form id="tagged_posts_filter" method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>">
         <input type="hidden" name="page" value="my-plugin-tagged-posts">
         <input type="hidden" name="tagged_posts_filter" value="my_plugin_tagged_posts_filter">

         <label for="tag">Search by Tag:</label>
         <input type="text" id="tag" name="search_tag" value="<?php echo esc_attr($search_tag); ?>"
            placeholder="Enter a tag...">


         <label for="post_type_filter">Filter by Post Type:</label>
         <select id="post_type_filter" name="post_type_filter">
            <option value="">All</option>
            <?php
            // Display post type filter options
            foreach ($selected_post_types as $post_type) {
               $label = get_post_type_object($post_type)->labels->name;
               $selected = selected($post_type, $post_type_filter, false);
               echo '<option value="' . esc_attr($post_type) . '" ' . $selected . '>' . esc_html($label) . '</option>';
            }
            ?>
            <input type="submit" class="button" name="filter_submit" value="Filter">
         </select>

         <label for="sort_by">Sort by:</label>
         <select id="sort_by" name="sort_by">
            <option value="id" <?php selected('id', $sort_by); ?>>ID</option>
            <option value="title" <?php selected('title', $sort_by); ?>>Title</option>
            <option value="tag_reference" <?php selected('tag_reference', $sort_by); ?>>Tag Reference</option>
            <option value="post_type" <?php selected('post_type', $sort_by); ?>>Post Type</option>
         </select>
         <input type="submit" class="button" name="sort_submit" value="Sort">


      </form>


      <table class="wp-list-table widefat striped">
         <thead>
            <tr>
               <th><a href="<?php echo esc_url(add_query_arg('sort_by', 'id')); ?>">ID</a></th>
               <th><a href="<?php echo esc_url(add_query_arg('sort_by', 'title')); ?>">Title</a></th>
               <th><a href="<?php echo esc_url(add_query_arg('sort_by', 'tag_reference')); ?>">Tag Reference</a></th>
               <th><a href="<?php echo esc_url(add_query_arg('sort_by', 'post_type')); ?>">Post Type</a></th>
            </tr>
         </thead>

         <tbody>
            <?php
            if ($tagged_posts->have_posts()) {
               while ($tagged_posts->have_posts()) {
                  $tagged_posts->the_post();

                  // Retrieve the meta value of the tag reference for the current post
                  $tag_reference = get_post_meta(get_the_ID(), 'my_plugin_tag_reference', true);

                  // If a search tag is specified and it doesn't match the current post's tag reference, skip this post
                  if (!empty($search_tag) && strtolower($tag_reference) !== strtolower($search_tag)) {
                     continue;
                  }
                  ?>
                  <tr>
                     <td>
                        <?php the_ID(); ?>
                     </td>
                     <td>
                        <?php the_title(); ?>
                     </td>
                     <td>
                        <?php echo $tag_reference; ?>
                     </td>
                     <td>
                        <?php echo get_post_type(); ?>
                     </td>
                  </tr>
                  <?php         
               }
            } else {
               ?>
               <tr>
                  <td colspan="4">No tagged posts found.</td>
               </tr>
               <?php
            }
            if (!empty($search_tag)) {
               // Display the reset button or link
               echo '<a href="' . admin_url('admin.php?page=my-plugin-tagged-posts') . '">' . __('All Tags') . '</a>';
            }
            // Reset post data
            wp_reset_postdata();
            ?>
         </tbody>
      </table>
   </div>
   <?php
}


<?php

/**
 * Plugin Name: Podcast Automation
 * Description: Automatically create Podcasts
 * Author: Gentrit Biba
 * Version: 1.5
 */

define("PODCAST_AUTOMATION_PLUGIN_DIR", plugin_dir_path(__FILE__));
define("PODCAST_AUTOMATION_PLUGIN_URL", plugin_dir_url(__FILE__));
define("PODCAST_AUTOMATION_CUSTOM_POST_TYPE_NAME", "podcast");
define("PODCAST_AUTOMATION_CUSTOM_POST_TYPE_TAXONOMY", "podcast_category");




if ( ! function_exists( 'post_exists' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/post.php' );
}


require_once(PODCAST_AUTOMATION_PLUGIN_DIR . "/vendor/autoload.php");
use phpseclib3\Net\SFTP;
function podcast_automation_job()
{
    if(!post_type_exists( "podcast" )){
        exit("Post type podcast does not exist");
    }
    include_once(PODCAST_AUTOMATION_PLUGIN_DIR . "/sftp_cred.php");
    $recursive = true;
    
    // Login to SFTP
    $sftp = new SFTP($host);
    if (!$sftp->login($username, $password)) {
        exit('Login Failed');
    }

    // LIST THE SELECTED DIRECTORY
    $dir_list = $sftp->nlist($path, true);

    // We sort the directory so we have the older podacsts last
    sort($dir_list);
    $files = array();

    
    foreach ($dir_list as $el) {
        $temp_a = explode("/", $el);
        if (!$files[$temp_a[0]]) {
            $files[$temp_a[0]] = array();
        }
        array_push($files[$temp_a[0]], $temp_a[1]);
    }

    $keys = array_keys($files);

    foreach ($keys as $key) {
        $arr_size = count($files[$key]);
        for ($i = 0; $i < $arr_size; $i++) {

            if (strpos($files[$key][$arr_size - 1 - $i], ".mp3") === 'false') {
                continue;
            }
            $temp_name = $files[$key][$arr_size - 1 - $i];
            // Remove m-
            $temp_name = str_replace('m-', "", $temp_name);
            // Remove .mp3
            $temp_name = str_replace('.mp3', "", $temp_name);
            $post_name = ucfirst($key) . " " . $temp_name;
            // Check if the post exists
            if (post_exists($post_name, '','',PODCAST_AUTOMATION_CUSTOM_POST_TYPE_NAME)) {
                break;
            } else {
                // Create the post type and give the selected category
                $tax = term_exists( $key, PODCAST_AUTOMATION_CUSTOM_POST_TYPE_TAXONOMY );
                $tax_id;
                $post_id = wp_insert_post(array(
                    'post_type' => PODCAST_AUTOMATION_CUSTOM_POST_TYPE_NAME,
                    'post_status'   => "publish",
                    'post_title' => $post_name,
                    'post_name' => $post_name,
                ));
                wp_set_post_terms($post_id, $key, PODCAST_AUTOMATION_CUSTOM_POST_TYPE_TAXONOMY);
                
            }
        }
    }

    // var_dump($keys); // == $sftp->nlist('.')
    // print_r($sftp->rawlist()); // == $sftp->rawlist('.')
}

add_action( 'init', 'create_topics_nonhierarchical_taxonomy', 0 );
 
function create_topics_nonhierarchical_taxonomy() {
 
// Labels part for the GUI
 
  $labels = array(
    'name' => _x( 'Podcast Category', 'taxonomy general name' ),
    'singular_name' => _x( 'Podcast Category', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search Podcast Category' ),
    'popular_items' => __( 'Popular Podcast Categories' ),
    'all_items' => __( 'All Podcast Categorie' ),
    'parent_item' => null,
    'parent_item_colon' => null,
    'edit_item' => __( 'Edit Podcast Category' ), 
    'update_item' => __( 'Update Podcast Category' ),
    'add_new_item' => __( 'Add New Podcast Category' ),
    'new_item_name' => __( 'New Podcast Category Name' ),
    'separate_items_with_commas' => __( 'Separate Podcast Categories with commas' ),
    'add_or_remove_items' => __( 'Add or remove Podcast Categories' ),
    'choose_from_most_used' => __( 'Choose from the most used Podcast Categories' ),
    'menu_name' => __( 'Podcast Categories' ),
  ); 
 
// Now register the non-hierarchical taxonomy like tag
 
  register_taxonomy(PODCAST_AUTOMATION_CUSTOM_POST_TYPE_TAXONOMY,PODCAST_AUTOMATION_CUSTOM_POST_TYPE_NAME,array(
    'hierarchical' => false,
    'labels' => $labels,
    'show_ui' => true,
    'show_in_rest' => true,
    'show_admin_column' => true,
    'update_count_callback' => '_update_post_term_count',
    'query_var' => true,
    'rewrite' => array( 'slug' => PODCAST_AUTOMATION_CUSTOM_POST_TYPE_TAXONOMY ),
  ));
}


// Temp test
function create_posttype() {
    register_post_type( 'podcast',
    // CPT Options
        array(
            'labels' => array(
                'name' => __( 'Podcast' ),
                'singular_name' => __( 'Podcast' )
            ),
            'taxonomies' => array(PODCAST_AUTOMATION_CUSTOM_POST_TYPE_TAXONOMY),
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => PODCAST_AUTOMATION_CUSTOM_POST_TYPE_NAME),
            'show_in_rest' => true,
 
        )
    );
    // var_dump(wp_insert_term("test1233", PODCAST_AUTOMATION_CUSTOM_POST_TYPE_TAXONOMY));
    // var_dump(podcast_automation_job());
    // wp_insert_term("test111", PODCAST_AUTOMATION_CUSTOM_POST_TYPE_TAXONOMY);
    // var_dump(wp_insert_post(array(
    //     'post_type' => PODCAST_AUTOMATION_CUSTOM_POST_TYPE_NAME,
    //     'tags_input' => array(PODCAST_AUTOMATION_CUSTOM_POST_TYPE_TAXONOMY =>array(8)),
    //     'post_status'   => "publish",
    //     'post_title' => "insert_here_later",
    //     'post_name' => "insert_here_later",
    // )));
    
    // var_dump(wp_mail('gentritbiba@gmail.com', "test", "test"));

}
// Hooking up our function to theme setup
add_action( 'init', 'create_posttype' );

function cron_test(){
    podcast_automation_job();
}
add_action('test_cron_job', 'cron_test');

// var_dump( term_exists( "test", PODCAST_AUTOMATION_CUSTOM_POST_TYPE_TAXONOMY ));

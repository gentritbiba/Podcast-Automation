<?php

/**
 * Plugin Name: Podcast Automation
 * Description: Automatically create 
 * Plugin URI: https://shorten.rest/introducing-the-wordpress-branded-sharebox/
 * Author: Shorten.REST
 * Author URI: https://shorten.rest
 * Version: 1.5
 */

define("PODCAST_AUTOMATION_PLUGIN_DIR", plugin_dir_path(__FILE__));
define("PODCAST_AUTOMATION_PLUGIN_URL", plugin_dir_url(__FILE__));
require_once(PODCAST_AUTOMATION_PLUGIN_DIR . "/vendor/autoload.php");
use phpseclib3\Net\SFTP;
function podcast_automation_job()
{
    if(!post_type_exists( "podcast" )){
        exit("Post type podcast does not exist");
    }
    include_once(PODCAST_AUTOMATION_PLUGIN_DIR . "/stfp_cred.php");
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

            // Check if the post exists
            if (!strpos($files[$key][$arr_size - 1 - $i], ".mp3")) {
                continue;
            }

            if ($files[$key][$arr_size - 1 - $i] || !$files[$key][0]) {
                break;
            } else {
                // Create the post type and give the selected category
            }
        }
    }

    var_dump($keys); // == $sftp->nlist('.')
    // print_r($sftp->rawlist()); // == $sftp->rawlist('.')
}


// Temp test
function create_posttype() {
    $labels = array(
        'name' => _x( 'Show Type', 'taxonomy general name' ),
        'singular_name' => _x( 'Recording', 'taxonomy singular name' ),
        'search_items' =>  __( 'Search Show Type' ),
        'popular_items' => __( 'Popular Show Types' ),
        'all_items' => __( 'All Show Types' ),
        'parent_item' => __( 'Parent Recording' ),
        'parent_item_colon' => __( 'Parent Recording:' ),
        'edit_item' => __( 'Edit Recording' ),
        'update_item' => __( 'Update Recording' ),
        'add_new_item' => __( 'Add New Recording' ),
        'new_item_name' => __( 'New Recording Name' ),
      );
    register_taxonomy('show_category',array('podcast'), array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'query_var' => true,
        'rewrite' => array( 'slug' => 'show_category' ),
      ));
    register_post_type( 'podcast',
    // CPT Options
        array(
            'labels' => array(
                'name' => __( 'podcast' ),
                'singular_name' => __( 'Podcast' )
            ),
            'taxonomies' => array('show_category'),
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'podcast'),
            'show_in_rest' => true,
 
        )
    );
}
// Hooking up our function to theme setup
add_action( 'init', 'create_posttype' );
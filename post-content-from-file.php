<?php
/**
 * @package Post_Content_From_File
 * @version 1.2
 */
/*
Plugin Name: Post Content from File
Description: This plugin allows you to use the content of a text file as the content of a post.
Author: Jon Long
Version: 1.2
Author URI: http://www.jon-long.ca
*/

$upload_dir_arr = wp_upload_dir();

$pcff_base_dir = $upload_dir_arr['basedir'] . '/post-content-from-file';

function post_content_from_file_shortcode( $atts ) {

	global $pcff_base_dir;

	extract(shortcode_atts(array(
		'filename' => '',
		'name' => '',
	), $atts));

	if ( strlen( $filename ) > 0 ) {
		// Get the content of the file, evaluate it, and return it
		$filepath = $pcff_base_dir . '/' . $filename;
	} elseif ( strlen( $name ) > 0 ) {
		$filepath = $pcff_base_dir . '/' . $name . '.wp.html';
	} else {
		return false;
	}

	if ( file_exists( $filepath ) ) {
		return do_shortcode( file_get_contents( $filepath ) );
	}
}

add_shortcode( 'post_content_from_file', 'post_content_from_file_shortcode');

add_shortcode( 'pcff', 'post_content_from_file_shortcode');


/**
 * If there is a file in the upload folder that matches the url of the post, load it
 */
function auto_load_post_file( $content ) {

	global $pcff_base_dir;

	$current_post_uri = str_replace(home_url(), '', get_permalink());
	$filename     = rtrim( $current_post_uri, '/') . '.wp.html';
	$filename_alt = rtrim( $current_post_uri, '/') . '/index.wp.html';
	$filepath = $pcff_base_dir . $filename;
	$filepath_alt = $pcff_base_dir . $filename_alt;
	if ( file_exists( $filepath ) ) {
		return file_get_contents( $filepath );
	} elseif ( file_exists( $filepath_alt ) ) {
		return file_get_contents( $filepath_alt );
	} else {
		return $content;
	}
}

add_filter( 'the_content', 'auto_load_post_file' );

/**
 * If the post is empty, still load corresponding file if found.
 */

add_action( 'the_post', function() {
	global $post;
	$cc = get_the_content();
	if ( $cc == '' ) {
		$content = auto_load_post_file( '' );
		if ($content) $post->post_content = $content;
	}
});

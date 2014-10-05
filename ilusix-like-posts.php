<?php
/**
 * Plugin Name: Ilusix like posts
 * Description: Like posts
 * Version: 1.0
 * Author: Sjors Roelofs
 * Author URI: http://www.ilusix.nl
 * License: MIT
 
    The MIT License (MIT)
    
    Copyright (c) 2014 Sjors Roelofs (sjors.roelofs@gmail.com)
    
    Permission is hereby granted, free of charge, to any person obtaining a copy of
    this software and associated documentation files (the "Software"), to deal in
    the Software without restriction, including without limitation the rights to
    use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
    the Software, and to permit persons to whom the Software is furnished to do so,
    subject to the following conditions:
    
    The above copyright notice and this permission notice shall be included in all
    copies or substantial portions of the Software.
    
    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
    FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
    COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
    IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
    CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */


// Variables
$ilpbLikeText     = 'Like';
$ilpbUnlikeText   = 'Unlike';
$ixMetaKeyName    = 'ix_post_likes';


/**
 * TODO:
 *
 *
 * Add WordPress plugin options page
 * Edit like texts in the WordPress admin
 *
 *
 *
 */




// Add the ajaxurl to the head
add_action( 'wp_head', 'ilp_set_ajaxurl' );
function ilp_set_ajaxurl() {
    echo "<script>var ilp_ajaxurl = '" . admin_url('admin-ajax.php') . "';</script>\n";
}


// Load custom css and javascript
add_action( 'wp_enqueue_scripts', 'ilp_load_scripts' );
function ilp_load_scripts() {
	wp_enqueue_style( 'ilp_css', plugin_dir_url( __FILE__ ) . 'css/ilusix-like-posts.css' );
    wp_enqueue_script( 'ilp_javascript', plugin_dir_url( __FILE__ ) . 'js/ilusix-like-posts.js', array( 'jquery' ) );
}


// Process the click on a like button (callback function from admin-ajax)
add_action( 'wp_ajax_ix_like_post', '_ix_process_like' );
function _ix_process_like() {
    global $ixMetaKeyName;

    $postId          = $_POST['data']['postid'];
    $currentUserId   = get_current_user_id();
    $likePost        = ($_POST['data']['likepost'] == 'true') ? true : false;
    
    if(isset($postId) && isset($currentUserId)) {
        $postLikesArray = _ix_get_post_likes_value($postId);
        
        if($likePost) {
            if(!in_array( $currentUserId, $postLikesArray )) $postLikesArray[] = $currentUserId;
        } else {
            // Remove the current user from the array
            if(in_array( $currentUserId, $postLikesArray )) $postLikesArray = array_diff( $postLikesArray, array( $currentUserId ) );
        }
        
        update_post_meta( $postId, $ixMetaKeyName, serialize( $postLikesArray ) );
    }
    
    exit;
}


// Get the amount of likes from a post
function _ix_get_post_likes_count($postId) { 
    return count( _ix_get_post_likes_value( $postId ) );
}


// Check if a user likes a post
function _ix_does_user_likes_post($postId) {    
    $currentUserId    = get_current_user_id();
    $postLikesArray   = _ix_get_post_likes_value($postId);
    
    if(in_array( $currentUserId, $postLikesArray )) return true;
    else return false;
}


// Get the current post likes value
function _ix_get_post_likes_value($postId) {
    global $ixMetaKeyName;
    
    $postLikesArray = unserialize( get_post_meta( $postId, $ixMetaKeyName, true ) );
    if(empty( $postLikesArray )) $postLikesArray = array();
    
    return $postLikesArray;
}


// Show the like button (frontend function)
function ix_like_button() {
    if(is_user_logged_in()) {
        global $post;
        global $ilpbLikeText;
        global $ilpbUnlikeText;

        $postId                 = $post->ID;
        $likeCount              = _ix_get_post_likes_count($postId);
        $currentUserLikesPost   = _ix_does_user_likes_post($postId);

        if ($postId != null) echo '<div class="ilpb postid-' . $postId . ' ' . (($currentUserLikesPost) ? 'like' : 'no-like') . '"><span class="ilpb-like-text">' . (($currentUserLikesPost) ? $ilpbUnlikeText : $ilpbLikeText) . '</span> (<span class="ilpb-like-count">' . $likeCount . '</span>)</div>';
    }
}
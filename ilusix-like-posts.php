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
$ilpbMetaKeyName          = 'ix_post_likes';
$ilpbSettingsField        = $ilpbMetaKeyName . '_settings_field';
$ilpbSettingsOptionName   = $ilpbMetaKeyName . '_plugin_settings';
$likeButtonTexts          = _ix_posts_like_get_button_text();
$ilpbLikeText             = $likeButtonTexts[0];
$ilpbUnlikeText           = $likeButtonTexts[1];


// Add the ajaxurl to the head
add_action( 'wp_head', 'ilp_set_ajaxurl' );
function ilp_set_ajaxurl() {
    global $ilpbLikeText;
    global $ilpbUnlikeText;

    echo "
        <script>
            var ilp_ajaxurl       = '" . admin_url('admin-ajax.php') . "';
            var ilp_like_text     = '" . $ilpbLikeText . "';
            var ilp_unlike_text   = '" . $ilpbUnlikeText . "';
        </script>
    \n";
}


// Load custom css and javascript
add_action( 'wp_enqueue_scripts', '_ix_posts_like_load_scripts' );
function _ix_posts_like_load_scripts() {
	wp_enqueue_style( 'ilp_css', plugin_dir_url( __FILE__ ) . 'css/ilusix-like-posts.css' );
    wp_enqueue_script( 'ilp_javascript', plugin_dir_url( __FILE__ ) . 'js/ilusix-like-posts.js', array( 'jquery' ) );
}


// Process the click on a like button (callback function from admin-ajax)
add_action( 'wp_ajax_ix_like_post', '_ix_posts_like_process_like' );
function _ix_posts_like_process_like() {
    global $ilpbMetaKeyName;

    $postId          = $_POST['data']['postid'];
    $currentUserId   = get_current_user_id();
    $likePost        = ($_POST['data']['likepost'] == 'true') ? true : false;
    
    if(isset($postId) && isset($currentUserId)) {
        $postLikesArray = _ix_posts_like_get_post_likes_value($postId);
        
        if($likePost) {
            if(!in_array( $currentUserId, $postLikesArray )) $postLikesArray[] = $currentUserId;
        } else {
            // Remove the current user from the array
            if(in_array( $currentUserId, $postLikesArray )) $postLikesArray = array_diff( $postLikesArray, array( $currentUserId ) );
        }
        
        update_post_meta( $postId, $ilpbMetaKeyName, serialize( $postLikesArray ) );
    }
    
    exit;
}


// Get the amount of likes from a post
function _ix_posts_like_get_post_likes_count( $postId ) {
    return count( _ix_posts_like_get_post_likes_value( $postId ) );
}


// Check if a user likes a post
function _ix_posts_like_does_user_likes_post( $postId ) {
    $currentUserId    = get_current_user_id();
    $postLikesArray   = _ix_posts_like_get_post_likes_value($postId);
    
    if(in_array( $currentUserId, $postLikesArray )) return true;
    else return false;
}


// Get the current post likes value
function _ix_posts_like_get_post_likes_value( $postId ) {
    global $ilpbMetaKeyName;
    
    $postLikesArray = unserialize( get_post_meta( $postId, $ilpbMetaKeyName, true ) );
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
        $likeCount              = _ix_posts_like_get_post_likes_count($postId);
        $currentUserLikesPost   = _ix_posts_like_does_user_likes_post($postId);

        if($postId != null) echo '<div class="ilpb postid-' . $postId . ' ' . (($currentUserLikesPost) ? 'like' : 'no-like') . '"><span class="ilpb-like-text">' . (($currentUserLikesPost) ? $ilpbUnlikeText : $ilpbLikeText) . '</span> (<span class="ilpb-like-count">' . $likeCount . '</span>)</div>';
    }
}


// Get the like/unlike button texts
function _ix_posts_like_get_button_text() {
    global $ilpbSettingsOptionName;

    $result          = array();
    $pluginOptions   = get_option( $ilpbSettingsOptionName );
    $result[]        = (isset( $pluginOptions['ix_post_likes_plugin_settings_like_text'] ) && !empty( $pluginOptions['ix_post_likes_plugin_settings_like_text'] )) ? $pluginOptions['ix_post_likes_plugin_settings_like_text'] : 'Like';
    $result[]        = (isset( $pluginOptions['ix_post_likes_plugin_settings_unlike_text'] ) && !empty( $pluginOptions['ix_post_likes_plugin_settings_unlike_text'] )) ? $pluginOptions['ix_post_likes_plugin_settings_unlike_text'] : 'Unlike';

    return $result;
}


// Create a plugin settings page
add_action('admin_menu', '_ix_posts_like_add_theme_settings_page');
function _ix_posts_like_add_theme_settings_page() {
    global $ilpbMetaKeyName;

    add_options_page(
        'Like posts options',
        'Like posts options',
        'manage_options',
        $ilpbMetaKeyName,
        '_ix_posts_like_settings_page_callback'
    );
}


// Plugin settings page callback
function _ix_posts_like_settings_page_callback() {
    global $ilpbSettingsField;
    global $ilpbMetaKeyName;

    echo '<div class="wrap">';
        echo '<h2>Post like settings</h2>';
        echo '<form action="options.php" method="post">';

            settings_fields( $ilpbSettingsField );
            do_settings_sections( $ilpbMetaKeyName );

            echo '<input name="Submit" type="submit" class="button button-primary" value="' . __('Save Changes') . '" />';
        echo '</form>';
    echo '</div><!-- div.wrap -->';
}


// Set the theme settings
add_action( 'admin_init', '_ix_posts_like_add_theme_settings' );
function _ix_posts_like_add_theme_settings() {
    global $ilpbSettingsField;
    global $ilpbSettingsOptionName;
    global $ilpbMetaKeyName;

    register_setting( $ilpbSettingsField, $ilpbSettingsOptionName );

    // Add the global settings section
    add_settings_section(
        $ilpbMetaKeyName . '_global_plugin_settings',
        '',
        function() {},
        $ilpbMetaKeyName
    );

    // Add a section field for the like text
    add_settings_field(
        $ilpbSettingsOptionName . '_like_text',
        'Like text',
        function() {
            global $ilpbSettingsOptionName;

            $options = get_option( $ilpbSettingsOptionName );
            echo '<input name="' . $ilpbSettingsOptionName . '[' . $ilpbSettingsOptionName . '_like_text' . ']" size="20" type="text" value="' . (isset($options[$ilpbSettingsOptionName . '_like_text']) ? $options[$ilpbSettingsOptionName . '_like_text'] : '') . '" /> <em>Default: \'Like\'</em>';
        },
        $ilpbMetaKeyName,
        $ilpbMetaKeyName . '_global_plugin_settings'
    );

    // Add a section field for the unlike text
    add_settings_field(
        $ilpbSettingsOptionName . '_unlike_text',
        'Unlike text',
        function() {
            global $ilpbSettingsOptionName;

            $options = get_option( $ilpbSettingsOptionName );
            echo '<input name="' . $ilpbSettingsOptionName . '[' . $ilpbSettingsOptionName . '_unlike_text' . ']" size="20" type="text" value="' . (isset($options[$ilpbSettingsOptionName . '_unlike_text']) ? $options[$ilpbSettingsOptionName . '_unlike_text'] : '') . '" /> <em>Default: \'Unlike\'</em>';
        },
        $ilpbMetaKeyName,
        $ilpbMetaKeyName . '_global_plugin_settings'
    );
}
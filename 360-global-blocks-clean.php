<?php
/*
Plugin Name: 360 Global Blocks
Description: Custom Gutenberg blocks for the 360 network. 
Version: 1.1.0
Author: Kaz Alvis
*/

if ( ! defined( 'ABSPATH' ) ) exit;

// Helper function to get YouTube embed URL
if (!function_exists('global360blocks_get_youtube_embed_url')) {
    function global360blocks_get_youtube_embed_url($url) {
        if (empty($url)) return '';
        
        $video_id = '';
        
        if (strpos($url, 'youtube.com/watch?v=') !== false) {
            $video_id = explode('v=', $url)[1];
            $video_id = explode('&', $video_id)[0];
        } elseif (strpos($url, 'youtu.be/') !== false) {
            $video_id = explode('youtu.be/', $url)[1];
            $video_id = explode('?', $video_id)[0];
        } elseif (strpos($url, 'youtube.com/embed/') !== false) {
            return $url; // Already an embed URL
        }
        
        return !empty($video_id) ? 'https://www.youtube.com/embed/' . $video_id : $url;
    }
}
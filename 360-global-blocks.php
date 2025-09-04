<?php
/*
Plugin Name: 360 Global Blocks
Description: Custom Gutenberg blocks for the 360 network. 
Version: 1.0.0
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

function global360blocks_render_popular_practices_block( $attributes, $content ) {
    $title = !empty($attributes['title']) ? esc_html($attributes['title']) : 'Popular Practices';
    $clinics = !empty($attributes['clinics']) ? $attributes['clinics'] : [];
    
    // Get all clinic posts
    $clinic_pages = get_posts(array(
        'post_type' => 'clinic',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'rand'
    ));
    
    // If no clinic CPT, fallback to pages/posts with clinic keywords
    if (empty($clinic_pages)) {
        $clinic_pages = get_posts(array(
            'post_type' => array('page', 'post'),
            'posts_per_page' => 20,
            'orderby' => 'rand',
            's' => 'clinic practice medical'
        ));
    }
    
    $output = '<div class="wp-block-global360blocks-popular-practices popular-practices-block">';
    $output .= '<div class="popular-practices-content">';
    $output .= '<h2 class="popular-practices-title">' . $title . '</h2>';
    $output .= '<div class="practices-grid">';
    
    foreach ($clinics as $index => $clinic) {
        $clinic_id = !empty($clinic['clinicId']) ? intval($clinic['clinicId']) : '';
        $custom_name = !empty($clinic['customName']) ? esc_html($clinic['customName']) : '';
        $custom_logo = !empty($clinic['customLogo']) ? esc_url($clinic['customLogo']) : '';
        $custom_url = !empty($clinic['customUrl']) ? esc_url($clinic['customUrl']) : '';
        
        // Determine clinic details
        if ($clinic_id && $clinic_page = get_post($clinic_id)) {
            $clinic_name = $custom_name ?: get_the_title($clinic_page);
            $clinic_url = $custom_url ?: get_permalink($clinic_page);
            
            // Use custom logo or try the clinic meta logo system
            if ($custom_logo) {
                $clinic_logo_url = $custom_logo;
            } elseif (function_exists('cpt360_get_clinic_logo_url')) {
                $clinic_logo_url = cpt360_get_clinic_logo_url($clinic_page->ID);
            } else {
                $clinic_logo_url = get_the_post_thumbnail_url($clinic_page, 'medium');
            }
        } else {
            // Use random clinic if available
            if (!empty($clinic_pages)) {
                $random_clinic = $clinic_pages[array_rand($clinic_pages)];
                $clinic_name = $custom_name ?: get_the_title($random_clinic);
                $clinic_url = $custom_url ?: get_permalink($random_clinic);
                
                // Use custom logo or try the clinic meta logo system for random clinic
                if ($custom_logo) {
                    $clinic_logo_url = $custom_logo;
                } elseif (function_exists('cpt360_get_clinic_logo_url')) {
                    $clinic_logo_url = cpt360_get_clinic_logo_url($random_clinic->ID);
                } else {
                    $clinic_logo_url = get_the_post_thumbnail_url($random_clinic, 'medium');
                }
            } else {
                $clinic_name = $custom_name ?: 'Sample Clinic ' . ($index + 1);
                $clinic_url = $custom_url ?: '#';
                $clinic_logo_url = $custom_logo ?: '';
            }
        }
        
        $output .= '<a href="' . $clinic_url . '" class="practice-card">';
        $output .= '<div class="practice-logo">';
        if ($clinic_logo_url) {
            $output .= '<img src="' . $clinic_logo_url . '" alt="' . esc_attr($clinic_name) . ' Logo" />';
        } else {
            $output .= '<div class="logo-placeholder">Logo</div>';
        }
        $output .= '</div>';
        $output .= '<h3 class="practice-name">' . $clinic_name . '</h3>';
        $output .= '</a>';
    }
    
    $output .= '</div>';
    $output .= '</div>';
    $output .= '</div>';
    
    return $output;
}

// Render callback for Two Column Slider block
function global360blocks_render_two_column_slider_block($attributes) {
    $slides = !empty($attributes['slides']) ? $attributes['slides'] : [];
    $autoplay = !empty($attributes['autoplay']) ? $attributes['autoplay'] : true;
    $autoplay_speed = !empty($attributes['autoplaySpeed']) ? intval($attributes['autoplaySpeed']) : 5000;
    $show_dots = !empty($attributes['showDots']) ? $attributes['showDots'] : true;
    $show_arrows = !empty($attributes['showArrows']) ? $attributes['showArrows'] : true;
    
    if (empty($slides)) {
        return '';
    }
    
    $output = '<div class="wp-block-global360blocks-two-column-slider">';
    $output .= '<div class="two-column-slider-container">';
    $output .= '<div class="slider-wrapper">';
    
    if ($show_arrows) {
        $output .= '<button class="slider-nav prev" onclick="previousSlide(this)" aria-label="Previous slide">‹</button>';
    }
    
    $output .= '<div class="slide-container">';
    
    foreach ($slides as $index => $slide) {
        $heading = !empty($slide['heading']) ? esc_html($slide['heading']) : '';
        $text = !empty($slide['text']) ? esc_html($slide['text']) : '';
        $image_url = !empty($slide['imageUrl']) ? esc_url($slide['imageUrl']) : '';
        
        $active_class = $index === 0 ? 'active' : '';
        
        $output .= '<div class="slide ' . $active_class . '" data-slide="' . $index . '">';
        $output .= '<div class="slide-content">';
        if ($heading) {
            $output .= '<h2 class="slide-heading">' . $heading . '</h2>';
        }
        if ($text) {
            $output .= '<p class="slide-text">' . $text . '</p>';
        }
        $output .= '</div>';
        
        if ($image_url) {
            $output .= '<div class="slide-image">';
            $output .= '<img src="' . $image_url . '" alt="' . esc_attr($heading) . '" />';
            $output .= '</div>';
        }
        $output .= '</div>';
    }
    
    $output .= '</div>';
    
    if ($show_arrows) {
        $output .= '<button class="slider-nav next" onclick="nextSlide(this)" aria-label="Next slide">›</button>';
    }
    
    $output .= '</div>';
    
    if ($show_dots) {
        $output .= '<div class="slider-dots">';
        foreach ($slides as $index => $slide) {
            $active_class = $index === 0 ? 'active' : '';
            $output .= '<button class="dot ' . $active_class . '" onclick="goToSlide(this, ' . $index . ')" aria-label="Go to slide ' . ($index + 1) . '"></button>';
        }
        $output .= '</div>';
    }
    
    $output .= '</div>';
    
    // Add slider JavaScript
    $output .= '<script>
        function nextSlide(button) {
            const container = button.closest(".two-column-slider-container");
            const slides = container.querySelectorAll(".slide");
            const dots = container.querySelectorAll(".dot");
            let current = 0;
            
            slides.forEach((slide, index) => {
                if (slide.classList.contains("active")) {
                    current = index;
                }
            });
            
            slides[current].classList.remove("active");
            if (dots[current]) dots[current].classList.remove("active");
            
            current = (current + 1) % slides.length;
            
            slides[current].classList.add("active");
            if (dots[current]) dots[current].classList.add("active");
        }
        
        function previousSlide(button) {
            const container = button.closest(".two-column-slider-container");
            const slides = container.querySelectorAll(".slide");
            const dots = container.querySelectorAll(".dot");
            let current = 0;
            
            slides.forEach((slide, index) => {
                if (slide.classList.contains("active")) {
                    current = index;
                }
            });
            
            slides[current].classList.remove("active");
            if (dots[current]) dots[current].classList.remove("active");
            
            current = current === 0 ? slides.length - 1 : current - 1;
            
            slides[current].classList.add("active");
            if (dots[current]) dots[current].classList.add("active");
        }
        
        function goToSlide(button, index) {
            const container = button.closest(".two-column-slider-container");
            const slides = container.querySelectorAll(".slide");
            const dots = container.querySelectorAll(".dot");
            
            slides.forEach(slide => slide.classList.remove("active"));
            dots.forEach(dot => dot.classList.remove("active"));
            
            slides[index].classList.add("active");
            dots[index].classList.add("active");
        }
        
        // Auto-play functionality
        ' . ($autoplay ? '
        document.addEventListener("DOMContentLoaded", function() {
            const containers = document.querySelectorAll(".two-column-slider-container");
            containers.forEach(container => {
                if (container.querySelector(".slide")) {
                    setInterval(() => {
                        const nextButton = container.querySelector(".slider-nav.next");
                        if (nextButton) {
                            nextSlide(nextButton);
                        }
                    }, ' . $autoplay_speed . ');
                }
            });
        });
        ' : '') . '
    </script>';
    
    $output .= '</div>';
    
    return $output;
}

// Helper function to convert YouTube URLs to embed format
if (!function_exists('get_youtube_embed_url')) {
    function get_youtube_embed_url($url) {
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

// Helper function to check if URL is a YouTube URL
if (!function_exists('global360blocks_is_youtube_url')) {
    function global360blocks_is_youtube_url($url) {
        return strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false;
    }
}

// Render callback for Latest Articles block
function global360blocks_render_latest_articles_block( $attributes, $content ) {
    $number_of_posts = isset($attributes['numberOfPosts']) ? (int) $attributes['numberOfPosts'] : 3;
    $show_excerpt = isset($attributes['showExcerpt']) ? $attributes['showExcerpt'] : true;
    $excerpt_length = isset($attributes['excerptLength']) ? (int) $attributes['excerptLength'] : 20;
    $columns = isset($attributes['columns']) ? (int) $attributes['columns'] : 3;
    
    // Query latest posts
    $posts = get_posts(array(
        'numberposts' => $number_of_posts,
        'post_status' => 'publish'
    ));
    
    if (empty($posts)) {
        return '<div class="latest-articles-block"><p>No articles found.</p></div>';
    }
    
    $output = '<div class="latest-articles-block" style="--columns: ' . $columns . ';">';
    $output .= '<div class="latest-articles-header">';
    $output .= '<h2>Our Latest Articles</h2>';
    $output .= '</div>';
    $output .= '<div class="latest-articles-grid">';
    
    foreach ($posts as $post) {
        $featured_image = get_the_post_thumbnail_url($post->ID, 'medium');
        $title = get_the_title($post->ID);
        $permalink = get_permalink($post->ID);
        
        $output .= '<article class="latest-article-item">';
        
        if ($featured_image) {
            $output .= '<div class="article-image">';
            $output .= '<a href="' . esc_url($permalink) . '">';
            $output .= '<img src="' . esc_url($featured_image) . '" alt="' . esc_attr($title) . '">';
            $output .= '</a>';
            $output .= '</div>';
        }
        
        $output .= '<div class="article-content">';
        $output .= '<h3 class="article-title">';
        $output .= '<a href="' . esc_url($permalink) . '">' . esc_html($title) . '</a>';
        $output .= '</h3>';
        
        if ($show_excerpt) {
            $excerpt = get_the_excerpt($post->ID);
            if (str_word_count($excerpt) > $excerpt_length) {
                $words = str_word_count($excerpt, 2);
                $excerpt = implode(' ', array_slice($words, 0, $excerpt_length)) . '...';
            }
            $output .= '<p class="article-excerpt">' . esc_html($excerpt) . '</p>';
        }
        
        $output .= '<div class="article-read-more">';
        $output .= '<a href="' . esc_url($permalink) . '" class="read-more-link">READ MORE →</a>';
        $output .= '</div>';
        
        $output .= '</div>';
        $output .= '</article>';
    }
    
    $output .= '</div>';
    $output .= '</div>';
    
    return $output;
}

// Render callback for Video Two Column block
function global360blocks_render_video_two_column_block( $attributes, $content ) {
    // Get Assessment ID from theme settings (360_global_settings array)
    $global_settings = get_option('360_global_settings', []);
    $assess_id = isset($global_settings['assessment_id']) ? $global_settings['assessment_id'] : '';
    
    $video_url = !empty($attributes['videoUrl']) ? esc_url($attributes['videoUrl']) : '';
    $heading = !empty($attributes['heading']) ? wp_kses_post($attributes['heading']) : '';
    $body_text = !empty($attributes['bodyText']) ? wp_kses_post($attributes['bodyText']) : '';
    
    $output = '<div class="video-two-column-block">';
    $output .= '<div class="video-two-column-container">';
    
    // Left column - Video
    $output .= '<div class="video-two-column-video">';
    if ($video_url) {
        if (global360blocks_is_youtube_url($video_url)) {
            $embed_url = global360blocks_get_youtube_embed_url($video_url);
            $output .= '<div class="video-wrapper">';
            $output .= '<iframe src="' . esc_url($embed_url) . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen class="youtube-video"></iframe>';
            $output .= '</div>';
        } else {
            $output .= '<div class="video-wrapper">';
            $output .= '<video controls class="column-video">';
            $output .= '<source src="' . $video_url . '" type="video/mp4">';
            $output .= 'Your browser does not support the video tag.';
            $output .= '</video>';
            $output .= '</div>';
        }
    }
    $output .= '</div>';
    
    // Right column - Content
    $output .= '<div class="video-two-column-content">';
    if ($heading) {
        $output .= '<h2 class="video-two-column-heading">' . $heading . '</h2>';
    }
    if ($body_text) {
        $output .= '<div class="video-two-column-body">' . $body_text . '</div>';
    }
    
    // Assessment button
    if (!empty($assess_id)) {
        $output .= '<div class="video-two-column-button">';
        $output .= '<pr360-questionnaire class="btn btn_global" url="wss://app.patientreach360.com/socket" site-id="' . esc_attr($assess_id) . '">Take Assessment</pr360-questionnaire>';
        $output .= '</div>';
    }
    
    $output .= '</div>';
    $output .= '</div>';
    $output .= '</div>';
    
    return $output;
}

// Render callback for Find Doctor block
function global360blocks_render_find_doctor_block($attributes) {
    $image_url = isset($attributes['imageUrl']) ? $attributes['imageUrl'] : '';
    $image_id = isset($attributes['imageId']) ? $attributes['imageId'] : 0;
    $heading = isset($attributes['heading']) ? $attributes['heading'] : '';
    $body_text = isset($attributes['bodyText']) ? $attributes['bodyText'] : '';

    $output = '<div class="find-doctor-block">';
    $output .= '<div class="find-doctor-container">';
    
    // Image column
    $output .= '<div class="find-doctor-image">';
    if ($image_url) {
        $alt_text = $heading ? esc_attr($heading) : 'Find Doctor Image';
        $output .= '<div class="image-wrapper">';
        $output .= '<img src="' . esc_url($image_url) . '" alt="' . $alt_text . '" />';
        $output .= '</div>';
    }
    $output .= '</div>';
    
    // Content column
    $output .= '<div class="find-doctor-content">';
    if ($heading) {
        $output .= '<h2 class="find-doctor-heading">' . wp_kses_post($heading) . '</h2>';
    }
    if ($body_text) {
        $output .= '<p class="find-doctor-body">' . wp_kses_post($body_text) . '</p>';
    }
    $output .= '<div class="find-doctor-button">';
    $output .= '<a href="/find-a-doctor/" class="btn btn_global">Find a Doctor</a>';
    $output .= '</div>';
    $output .= '</div>';
    
    $output .= '</div>';
    $output .= '</div>';

    return $output;
}

// Render callback for Medical Icons block
function global360blocks_render_medical_icons_block($attributes) {
    // Medical icons data - same as JavaScript
    $medical_icons = array(
        'stethoscope' => array(
            'name' => 'Stethoscope',
            'category' => 'Equipment',
            'outline' => '<path d="M8.5 1a2 2 0 1 0 0 4h.5v.5a6.5 6.5 0 0 0 13 0V5h.5a2 2 0 1 0 0-4h-14zM10 5V3h12v2a5 5 0 0 1-10 0z"/>',
            'filled' => '<path d="M8.5 1a2 2 0 1 0 0 4h.5v.5a6.5 6.5 0 0 0 13 0V5h.5a2 2 0 1 0 0-4h-14zM10 5V3h12v2a5 5 0 0 1-10 0z"/>'
        ),
        'heart' => array(
            'name' => 'Heart',
            'category' => 'Body',
            'outline' => '<path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>',
            'filled' => '<path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>'
        ),
        'thermometer' => array(
            'name' => 'Thermometer',
            'category' => 'Equipment',
            'outline' => '<path d="M14 14.76V3.5a2.5 2.5 0 0 0-5 0v11.26a4.5 4.5 0 1 0 5 0zM12 4a1 1 0 0 1 1 1v9.17a3 3 0 1 1-2 0V5a1 1 0 0 1 1-1z"/>',
            'filled' => '<path d="M14 14.76V3.5a2.5 2.5 0 0 0-5 0v11.26a4.5 4.5 0 1 0 5 0zM12 19a2 2 0 1 1-2-2 2 2 0 0 1 2 2z"/>'
        ),
        'pill' => array(
            'name' => 'Pill',
            'category' => 'Medicine',
            'outline' => '<path d="M8.5 1a7.5 7.5 0 0 0 0 15h7a7.5 7.5 0 0 0 0-15h-7zm0 2h7a5.5 5.5 0 0 1 0 11h-7a5.5 5.5 0 0 1 0-11z"/>',
            'filled' => '<path d="M8.5 1a7.5 7.5 0 0 0 0 15h3.75V1H8.5zm7 0v15a7.5 7.5 0 0 0 0-15z"/>'
        ),
        'syringe' => array(
            'name' => 'Syringe',
            'category' => 'Equipment',
            'outline' => '<path d="M2.5 7.5L7 12l1.5-1.5 7.5 7.5 3-3-7.5-7.5L12 7l4.5-4.5-3-3L9 4l.5 1.5L7 8l1.5 1.5z"/>',
            'filled' => '<path d="M2.5 7.5L7 12l1.5-1.5 7.5 7.5 3-3-7.5-7.5L12 7l4.5-4.5-3-3L9 4l.5 1.5L7 8l1.5 1.5z"/>'
        ),
        'first-aid' => array(
            'name' => 'First Aid Cross',
            'category' => 'Emergency',
            'outline' => '<path d="M3 3h18v18H3V3zm2 2v14h14V5H5zm6 2h2v4h4v2h-4v4h-2v-4H7v-2h4V7z"/>',
            'filled' => '<path d="M3 3h18v18H3V3zm8 4h2v4h4v2h-4v4h-2v-4H7v-2h4V7z"/>'
        ),
        'ambulance' => array(
            'name' => 'Ambulance',
            'category' => 'Emergency',
            'outline' => '<path d="M2 8h4l2-2h8l2 2h4v8h-2v2h-2v-2H6v2H4v-2H2V8zm2 2v4h2v-2h2v2h8v-2h2v2h2v-4h-3l-1-1H7l-1 1H3z"/>',
            'filled' => '<path d="M2 8h4l2-2h8l2 2h4v8h-2v2h-2v-2H6v2H4v-2H2V8zm9 1h2v2h2v2h-2v2h-2v-2H9v-2h2V9z"/>'
        ),
        'tooth' => array(
            'name' => 'Tooth',
            'category' => 'Dental',
            'outline' => '<path d="M12 2a6 6 0 0 0-6 6c0 1.5.5 3 1 4.5L8 16c.5 2 1.5 4 2 6h4c.5-2 1.5-4 2-6l1-3.5c.5-1.5 1-3 1-4.5a6 6 0 0 0-6-6z"/>',
            'filled' => '<path d="M12 2a6 6 0 0 0-6 6c0 1.5.5 3 1 4.5L8 16c.5 2 1.5 4 2 6h4c.5-2 1.5-4 2-6l1-3.5c.5-1.5 1-3 1-4.5a6 6 0 0 0-6-6z"/>'
        ),
        'bandage' => array(
            'name' => 'Bandage',
            'category' => 'Treatment',
            'outline' => '<path d="M3 8h2l2-2 8 8-2 2h-2l-8-8zm1 1l7 7h1l1-1L6 8H4zm8-6l3 3-1 1-3-3 1-1zm4 4l3 3-1 1-3-3 1-1z"/>',
            'filled' => '<path d="M3 8h2l2-2 8 8-2 2h-2l-8-8zm8-6l3 3-1 1-3-3 1-1zm4 4l3 3-1 1-3-3 1-1z"/>'
        ),
        'x-ray' => array(
            'name' => 'X-Ray',
            'category' => 'Imaging',
            'outline' => '<path d="M4 4h16v16H4V4zm2 2v12h12V6H6zm2 2h8v8H8V8zm2 2v4h4v-4h-4z"/>',
            'filled' => '<path d="M4 4h16v16H4V4zm4 4h8v8H8V8zm2 2v4h4v-4h-4z"/>'
        )
    );

    // Get block attributes
    $selected_icon = isset($attributes['selectedIcon']) ? $attributes['selectedIcon'] : 'stethoscope';
    $icon_style = isset($attributes['iconStyle']) ? $attributes['iconStyle'] : 'outline';
    $icon_size = isset($attributes['iconSize']) ? $attributes['iconSize'] : 48;
    $icon_color = isset($attributes['iconColor']) ? $attributes['iconColor'] : '#0073aa';
    $show_label = isset($attributes['showLabel']) ? $attributes['showLabel'] : true;
    $icon_label = isset($attributes['iconLabel']) ? $attributes['iconLabel'] : 'Medical Icon';
    $alignment = isset($attributes['alignment']) ? $attributes['alignment'] : 'center';

    // Get icon data
    $icon_data = isset($medical_icons[$selected_icon]) ? $medical_icons[$selected_icon] : $medical_icons['stethoscope'];
    $path_data = $icon_style === 'filled' ? $icon_data['filled'] : $icon_data['outline'];

    // Generate CSS classes
    $css_classes = array(
        'wp-block-global360blocks-medical-icons',
        'alignment-' . $alignment
    );

    if (isset($attributes['className'])) {
        $css_classes[] = $attributes['className'];
    }

    $wrapper_attributes = get_block_wrapper_attributes(array(
        'class' => implode(' ', $css_classes)
    ));

    // Render the block
    $output = '<div ' . $wrapper_attributes . '>';
    $output .= '<div class="medical-icon-display alignment-' . esc_attr($alignment) . '">';
    $output .= '<svg width="' . esc_attr($icon_size) . '" height="' . esc_attr($icon_size) . '" viewBox="0 0 24 24"';
    $output .= ' fill="' . ($icon_style === 'filled' ? esc_attr($icon_color) : 'none') . '"';
    $output .= ' stroke="' . ($icon_style === 'outline' ? esc_attr($icon_color) : 'none') . '"';
    $output .= ' stroke-width="2" stroke-linecap="round" stroke-linejoin="round" role="img" aria-labelledby="medical-icon-title">';
    $output .= '<title id="medical-icon-title">' . esc_html($icon_data['name']) . '</title>';
    $output .= $path_data;
    $output .= '</svg>';
    
    if ($show_label && !empty($icon_label)) {
        $output .= '<span class="icon-label">' . esc_html($icon_label) . '</span>';
    }
    
    $output .= '</div>';
    $output .= '</div>';

    return $output;
}

// Render callback for Info Cards block
function global360blocks_render_info_cards_block($attributes) {
    // Medical icons data - same as JavaScript
    $medical_icons = array(
        'stethoscope' => array(
            'name' => 'Stethoscope',
            'category' => 'Equipment',
            'path' => '<path d="M8.5 1a2 2 0 1 0 0 4h.5v.5a6.5 6.5 0 0 0 13 0V5h.5a2 2 0 1 0 0-4h-14zM10 5V3h12v2a5 5 0 0 1-10 0z"/>'
        ),
        'heart' => array(
            'name' => 'Heart',
            'category' => 'Body',
            'path' => '<path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>'
        ),
        'thermometer' => array(
            'name' => 'Thermometer',
            'category' => 'Equipment',
            'path' => '<path d="M14 14.76V3.5a2.5 2.5 0 0 0-5 0v11.26a4.5 4.5 0 1 0 5 0zM12 4a1 1 0 0 1 1 1v9.17a3 3 0 1 1-2 0V5a1 1 0 0 1 1-1z"/>'
        ),
        'pill' => array(
            'name' => 'Pill',
            'category' => 'Medicine',
            'path' => '<path d="M8.5 1a7.5 7.5 0 0 0 0 15h7a7.5 7.5 0 0 0 0-15h-7zm0 2h7a5.5 5.5 0 0 1 0 11h-7a5.5 5.5 0 0 1 0-11z"/>'
        ),
        'syringe' => array(
            'name' => 'Syringe',
            'category' => 'Equipment',
            'path' => '<path d="M2.5 7.5L7 12l1.5-1.5 7.5 7.5 3-3-7.5-7.5L12 7l4.5-4.5-3-3L9 4l.5 1.5L7 8l1.5 1.5z"/>'
        ),
        'first-aid' => array(
            'name' => 'First Aid Cross',
            'category' => 'Emergency',
            'path' => '<path d="M3 3h18v18H3V3zm2 2v14h14V5H5zm6 2h2v4h4v2h-4v4h-2v-4H7v-2h4V7z"/>'
        ),
        'ambulance' => array(
            'name' => 'Ambulance',
            'category' => 'Emergency',
            'path' => '<path d="M2 8h4l2-2h8l2 2h4v8h-2v2h-2v-2H6v2H4v-2H2V8zm2 2v4h2v-2h2v2h8v-2h2v2h2v-4h-3l-1-1H7l-1 1H3z"/>'
        ),
        'tooth' => array(
            'name' => 'Tooth',
            'category' => 'Dental',
            'path' => '<path d="M12 2a6 6 0 0 0-6 6c0 1.5.5 3 1 4.5L8 16c.5 2 1.5 4 2 6h4c.5-2 1.5-4 2-6l1-3.5c.5-1.5 1-3 1-4.5a6 6 0 0 0-6-6z"/>'
        ),
        'bandage' => array(
            'name' => 'Bandage',
            'category' => 'Treatment',
            'path' => '<path d="M3 8h2l2-2 8 8-2 2h-2l-8-8zm1 1l7 7h1l1-1L6 8H4zm8-6l3 3-1 1-3-3 1-1zm4 4l3 3-1 1-3-3 1-1z"/>'
        ),
        'x-ray' => array(
            'name' => 'X-Ray',
            'category' => 'Imaging',
            'path' => '<path d="M4 4h16v16H4V4zm2 2v12h12V6H6zm2 2h8v8H8V8zm2 2v4h4v-4h-4z"/>'
        ),
        'calendar' => array(
            'name' => 'Calendar',
            'category' => 'Schedule',
            'path' => '<path d="M3 4h18v16H3V4zm2 2v12h14V6H5zm2-4v2h2V2H7zm6 0v2h2V2h-2z"/>'
        ),
        'shield' => array(
            'name' => 'Shield',
            'category' => 'Protection',
            'path' => '<path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/>'
        )
    );

    // Get block attributes
    $main_title = isset($attributes['mainTitle']) ? $attributes['mainTitle'] : 'Why Choose Us';
    $cards = isset($attributes['cards']) ? $attributes['cards'] : array(
        array(
            'icon' => 'stethoscope',
            'title' => 'Expert Care',
            'text' => 'Our experienced medical professionals provide top-quality healthcare services.'
        ),
        array(
            'icon' => 'heart',
            'title' => 'Compassionate Service',
            'text' => 'We care about your wellbeing and provide personalized attention to every patient.'
        ),
        array(
            'icon' => 'first-aid',
            'title' => 'Emergency Ready',
            'text' => 'Available 24/7 for emergency situations with rapid response capabilities.'
        )
    );

    // Generate CSS classes
    $css_classes = array(
        'wp-block-global360blocks-info-cards'
    );

    if (isset($attributes['className'])) {
        $css_classes[] = $attributes['className'];
    }

    $wrapper_attributes = get_block_wrapper_attributes(array(
        'class' => implode(' ', $css_classes)
    ));

    // Render the block
    $output = '<div ' . $wrapper_attributes . '>';
    $output .= '<div class="info-cards-container">';
    
    if (!empty($main_title)) {
        $output .= '<h2 class="info-cards-main-title">' . esc_html($main_title) . '</h2>';
    }
    
    $output .= '<div class="info-cards-grid">';
    
    foreach ($cards as $card) {
        $icon_key = isset($card['icon']) ? $card['icon'] : 'stethoscope';
        $icon_data = isset($medical_icons[$icon_key]) ? $medical_icons[$icon_key] : $medical_icons['stethoscope'];
        $card_title = isset($card['title']) ? $card['title'] : '';
        $card_text = isset($card['text']) ? $card['text'] : '';
        
        $output .= '<div class="info-card">';
        $output .= '<div class="info-card-icon-wrapper">';
        $output .= '<svg width="64" height="64" viewBox="0 0 24 24"';
        $output .= ' fill="var(--cpt360-primary, #0073aa)" stroke="var(--cpt360-primary, #0073aa)"';
        $output .= ' stroke-width="2" stroke-linecap="round" stroke-linejoin="round"';
        $output .= ' class="info-card-icon" role="img" aria-labelledby="icon-title-' . esc_attr($icon_key) . '">';
        $output .= '<title id="icon-title-' . esc_attr($icon_key) . '">' . esc_html($icon_data['name']) . '</title>';
        $output .= $icon_data['path'];
        $output .= '</svg>';
        $output .= '</div>';
        
        if (!empty($card_title)) {
            $output .= '<h3 class="info-card-title">' . wp_kses_post($card_title) . '</h3>';
        }
        
        if (!empty($card_text)) {
            $output .= '<p class="info-card-text">' . wp_kses_post($card_text) . '</p>';
        }
        
        $output .= '</div>';
    }
    
    $output .= '</div>';
    $output .= '</div>';
    $output .= '</div>';

    return $output;
}

// Enqueue block editor assets
function global360blocks_enqueue_block_editor_assets() {
    if (file_exists( plugin_dir_path( __FILE__ ) . 'build/hero.js' )) {
        wp_enqueue_script(
            'global360blocks-hero',
            plugins_url( 'build/hero.js', __FILE__ ),
            array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n' ),
            filemtime( plugin_dir_path( __FILE__ ) . 'build/hero.js' )
        );
    }
    
    // Full Hero block assets
    if (file_exists( plugin_dir_path( __FILE__ ) . 'build/full-hero.js' )) {
        wp_enqueue_script(
            'global360blocks-full-hero',
            plugins_url( 'build/full-hero.js', __FILE__ ),
            array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n' ),
            filemtime( plugin_dir_path( __FILE__ ) . 'build/full-hero.js' )
        );
    }
    
    // CTA block assets
    if (file_exists( plugin_dir_path( __FILE__ ) . 'build/cta.js' )) {
        wp_enqueue_script(
            'global360blocks-cta',
            plugins_url( 'build/cta.js', __FILE__ ),
            array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n' ),
            filemtime( plugin_dir_path( __FILE__ ) . 'build/cta.js' )
        );
    }
    
    // Two Column block assets
    if (file_exists( plugin_dir_path( __FILE__ ) . 'build/two-column.js' )) {
        wp_enqueue_script(
            'global360blocks-two-column',
            plugins_url( 'build/two-column.js', __FILE__ ),
            array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n' ),
            filemtime( plugin_dir_path( __FILE__ ) . 'build/two-column.js' )
        );
    }
    
    // Video Two Column block assets
    if (file_exists( plugin_dir_path( __FILE__ ) . 'build/video-two-column.js' )) {
        wp_enqueue_script(
            'global360blocks-video-two-column',
            plugins_url( 'build/video-two-column.js', __FILE__ ),
            array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n' ),
            filemtime( plugin_dir_path( __FILE__ ) . 'build/video-two-column.js' )
        );
    }
    
    // Latest Articles block assets
    if (file_exists( plugin_dir_path( __FILE__ ) . 'build/latest-articles.js' )) {
        wp_enqueue_script(
            'global360blocks-latest-articles',
            plugins_url( 'build/latest-articles.js', __FILE__ ),
            array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-data' ),
            filemtime( plugin_dir_path( __FILE__ ) . 'build/latest-articles.js' )
        );
    }
    
    // Find Doctor block JavaScript
    if (file_exists( plugin_dir_path( __FILE__ ) . 'build/find-doctor.js' )) {
        wp_enqueue_script(
            'find-doctor-block-editor',
            plugin_dir_url(__FILE__) . 'build/find-doctor.js',
            array('wp-blocks', 'wp-element', 'wp-editor'),
            filemtime(plugin_dir_path(__FILE__) . 'build/find-doctor.js')
        );
    }
    
    // Enqueue editor styles
    if (file_exists( plugin_dir_path( __FILE__ ) . 'build/hero-editor.css' )) {
        wp_enqueue_style(
            'global360blocks-hero-editor-style',
            plugins_url( 'build/hero-editor.css', __FILE__ ),
            array( 'wp-edit-blocks' ),
            filemtime( plugin_dir_path( __FILE__ ) . 'build/hero-editor.css' )
        );
    }
    
    if (file_exists( plugin_dir_path( __FILE__ ) . 'build/full-hero-editor.css' )) {
        wp_enqueue_style(
            'global360blocks-full-hero-editor-style',
            plugins_url( 'build/full-hero-editor.css', __FILE__ ),
            array( 'wp-edit-blocks' ),
            filemtime( plugin_dir_path( __FILE__ ) . 'build/full-hero-editor.css' )
        );
    }
    
    if (file_exists( plugin_dir_path( __FILE__ ) . 'build/cta-editor.css' )) {
        wp_enqueue_style(
            'global360blocks-cta-editor-style',
            plugins_url( 'build/cta-editor.css', __FILE__ ),
            array( 'wp-edit-blocks' ),
            filemtime( plugin_dir_path( __FILE__ ) . 'build/cta-editor.css' )
        );
    }
    
    if (file_exists( plugin_dir_path( __FILE__ ) . 'build/two-column-editor.css' )) {
        wp_enqueue_style(
            'global360blocks-two-column-editor-style',
            plugins_url( 'build/two-column-editor.css', __FILE__ ),
            array( 'wp-edit-blocks' ),
            filemtime( plugin_dir_path( __FILE__ ) . 'build/two-column-editor.css' )
        );
    }
    
    if (file_exists( plugin_dir_path( __FILE__ ) . 'build/video-two-column-editor.css' )) {
        wp_enqueue_style(
            'global360blocks-video-two-column-editor-style',
            plugins_url( 'build/video-two-column-editor.css', __FILE__ ),
            array( 'wp-edit-blocks' ),
            filemtime( plugin_dir_path( __FILE__ ) . 'build/video-two-column-editor.css' )
        );
    }
    
    if (file_exists( plugin_dir_path( __FILE__ ) . 'build/latest-articles-editor.css' )) {
        wp_enqueue_style(
            'global360blocks-latest-articles-editor-style',
            plugins_url( 'build/latest-articles-editor.css', __FILE__ ),
            array( 'wp-edit-blocks' ),
            filemtime( plugin_dir_path( __FILE__ ) . 'build/latest-articles-editor.css' )
        );
    }
    
    // Find Doctor block editor CSS
    if (file_exists( plugin_dir_path( __FILE__ ) . 'build/find-doctor-editor.css' )) {
        wp_enqueue_style(
            'find-doctor-block-editor-css',
            plugin_dir_url(__FILE__) . 'build/find-doctor-editor.css',
            array(),
            filemtime(plugin_dir_path(__FILE__) . 'build/find-doctor-editor.css')
        );
    }
    
    // Medical Icons block JavaScript - commented out until built
    // if (file_exists( plugin_dir_path( __FILE__ ) . 'blocks/medical-icons/build/index.js' )) {
    //     wp_enqueue_script(
    //         'medical-icons-block-editor',
    //         plugins_url( 'blocks/medical-icons/build/index.js', __FILE__ ),
    //         array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components'),
    //         filemtime(plugin_dir_path(__FILE__) . 'blocks/medical-icons/build/index.js')
    //     );
    // }
    
    // Medical Icons block editor CSS - commented out until built
    // if (file_exists( plugin_dir_path( __FILE__ ) . 'blocks/medical-icons/build/index.css' )) {
    //     wp_enqueue_style(
    //         'medical-icons-block-editor-css',
    //         plugins_url( 'blocks/medical-icons/build/index.css', __FILE__ ),
    //         array(),
    //         filemtime(plugin_dir_path(__FILE__) . 'blocks/medical-icons/build/index.css')
    //     );
    // }
    
    // Info Cards block JavaScript
    if (file_exists( plugin_dir_path( __FILE__ ) . 'blocks/info-cards/build/index.js' )) {
        wp_enqueue_script(
            'info-cards-block-editor',
            plugins_url( 'blocks/info-cards/build/index.js', __FILE__ ),
            array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components'),
            filemtime(plugin_dir_path(__FILE__) . 'blocks/info-cards/build/index.js')
        );
    }
    
    // Info Cards block editor CSS
    if (file_exists( plugin_dir_path( __FILE__ ) . 'blocks/info-cards/build/index.css' )) {
        wp_enqueue_style(
            'info-cards-block-editor-css',
            plugins_url( 'blocks/info-cards/build/index.css', __FILE__ ),
            array(),
            filemtime(plugin_dir_path(__FILE__) . 'blocks/info-cards/build/index.css')
        );
    }
    
    // Popular Practices block JavaScript
    if (file_exists( plugin_dir_path( __FILE__ ) . 'blocks/popular-practices/build/index.js' )) {
        wp_enqueue_script(
            'popular-practices-block-editor',
            plugins_url( 'blocks/popular-practices/build/index.js', __FILE__ ),
            array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-data'),
            filemtime(plugin_dir_path(__FILE__) . 'blocks/popular-practices/build/index.js')
        );
    }
    
    // Two Column Slider block JavaScript
    if (file_exists( plugin_dir_path( __FILE__ ) . 'blocks/two-column-slider/build/index.js' )) {
        wp_enqueue_script(
            'two-column-slider-block-editor',
            plugins_url( 'blocks/two-column-slider/build/index.js', __FILE__ ),
            array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components'),
            filemtime(plugin_dir_path(__FILE__) . 'blocks/two-column-slider/build/index.js')
        );
    }
    
    // Two Column Slider block editor CSS
    if (file_exists( plugin_dir_path( __FILE__ ) . 'blocks/two-column-slider/build/index.css' )) {
        wp_enqueue_style(
            'two-column-slider-block-editor-css',
            plugins_url( 'blocks/two-column-slider/build/index.css', __FILE__ ),
            array(),
            filemtime(plugin_dir_path(__FILE__) . 'blocks/two-column-slider/build/index.css')
        );
    }
    
    // Popular Practices block editor CSS
    if (file_exists( plugin_dir_path( __FILE__ ) . 'blocks/popular-practices/build/index.css' )) {
        wp_enqueue_style(
            'popular-practices-block-editor-css',
            plugins_url( 'blocks/popular-practices/build/index.css', __FILE__ ),
            array(),
            filemtime(plugin_dir_path(__FILE__) . 'blocks/popular-practices/build/index.css')
        );
    }
    
    // Enqueue combined styles
    if (file_exists( plugin_dir_path( __FILE__ ) . 'build/hero.css' )) {
        wp_enqueue_style(
            'global360blocks-hero-style',
            plugins_url( 'build/hero.css', __FILE__ ),
            array( 'wp-edit-blocks' ),
            filemtime( plugin_dir_path( __FILE__ ) . 'build/hero.css' )
        );
    }
    
    if (file_exists( plugin_dir_path( __FILE__ ) . 'build/full-hero.css' )) {
        wp_enqueue_style(
            'global360blocks-full-hero-style',
            plugins_url( 'build/full-hero.css', __FILE__ ),
            array( 'wp-edit-blocks' ),
            filemtime( plugin_dir_path( __FILE__ ) . 'build/full-hero.css' )
        );
    }
    
    if (file_exists( plugin_dir_path( __FILE__ ) . 'build/cta.css' )) {
        wp_enqueue_style(
            'global360blocks-cta-style',
            plugins_url( 'build/cta.css', __FILE__ ),
            array( 'wp-edit-blocks' ),
            filemtime( plugin_dir_path( __FILE__ ) . 'build/cta.css' )
        );
    }
    
    if (file_exists( plugin_dir_path( __FILE__ ) . 'build/two-column.css' )) {
        wp_enqueue_style(
            'global360blocks-two-column-style',
            plugins_url( 'build/two-column.css', __FILE__ ),
            array( 'wp-edit-blocks' ),
            filemtime( plugin_dir_path( __FILE__ ) . 'build/two-column.css' )
        );
    }
    
    if (file_exists( plugin_dir_path( __FILE__ ) . 'build/latest-articles.css' )) {
        wp_enqueue_style(
            'global360blocks-latest-articles-style',
            plugins_url( 'build/latest-articles.css', __FILE__ ),
            array( 'wp-edit-blocks' ),
            filemtime( plugin_dir_path( __FILE__ ) . 'build/latest-articles.css' )
        );
    }
    
    // Find Doctor frontend CSS
    if (file_exists( plugin_dir_path( __FILE__ ) . 'build/find-doctor.css' )) {
        wp_enqueue_style(
            'find-doctor-block-css',
            plugin_dir_url(__FILE__) . 'build/find-doctor.css',
            array(),
            filemtime(plugin_dir_path(__FILE__) . 'build/find-doctor.css')
        );
    }
}
add_action( 'enqueue_block_editor_assets', 'global360blocks_enqueue_block_editor_assets' );

// Register custom block category for 360 Blocks (before blocks)
add_filter('block_categories_all', function($categories, $post) {
    return array_merge(
        $categories,
        array(
            array(
                'slug' => '360-blocks',
                'title' => __('360 Blocks', '360-global-blocks'),
            ),
        )
    );
}, 10, 2);

// Register block
function global360blocks_register_blocks() {
    // Register Test Hero block
    error_log('Registering Test Hero block with path: ' . __DIR__ . '/blocks/simple-hero/build');
    register_block_type( __DIR__ . '/blocks/simple-hero/build', array(
        'render_callback' => 'global360blocks_render_simple_hero_block',
    ));
    error_log('Test Hero block registration completed');
    
    register_block_type( __DIR__ . '/blocks/full-hero/build', array(
        'render_callback' => 'global360blocks_render_full_hero_block',
    ) );

    register_block_type( __DIR__ . '/blocks/cta/build', array(
        'render_callback' => 'global360blocks_render_cta_block',
    ) );

    register_block_type( __DIR__ . '/blocks/two-column/build', array(
        'render_callback' => 'global360blocks_render_two_column_block',
    ) );
    
    register_block_type( __DIR__ . '/blocks/video-two-column/build', array(
        'render_callback' => 'global360blocks_render_video_two_column_block',
    ) );
    
    register_block_type( __DIR__ . '/blocks/latest-articles/build', array(
        'render_callback' => 'global360blocks_render_latest_articles_block',
    ) );

    // Register Find Doctor block
    register_block_type( __DIR__ . '/blocks/find-doctor/build', array(
        'render_callback' => 'global360blocks_render_find_doctor_block',
    ));

    // Register Info Cards block
    register_block_type( __DIR__ . '/blocks/info-cards/build', array(
        'render_callback' => 'global360blocks_render_info_cards_block',
    ));
    
    register_block_type( __DIR__ . '/blocks/popular-practices/build', array(
        'render_callback' => 'global360blocks_render_popular_practices_block',
    ));
    
    // Register Two Column Slider block
    register_block_type( __DIR__ . '/blocks/two-column-slider/build', array(
        'render_callback' => 'global360blocks_render_two_column_slider_block',
    ));
}

// Render callback for CTA block
function global360blocks_render_cta_block( $attributes, $content ) {
    // Get Assessment ID from theme settings (360_global_settings array)
    $global_settings = get_option('360_global_settings', []);
    $assess_id = isset($global_settings['assessment_id']) ? $global_settings['assessment_id'] : '';
    
    $image_url = !empty($attributes['imageUrl']) ? esc_url($attributes['imageUrl']) : '';
    $heading = !empty($attributes['heading']) ? wp_kses_post($attributes['heading']) : '';
    
    $output = '<div class="cta-block">';
    $output .= '<div class="cta-container" style="background-image: url(' . $image_url . ');">';
    $output .= '<div class="cta-content">';
    if ($heading) {
        $output .= '<h2 class="cta-heading">' . $heading . '</h2>';
    }
    $output .= '<div class="cta-button">';
    $output .= '<pr360-questionnaire class="btn btn_global" url="wss://app.patientreach360.com/socket" site-id="' . esc_attr($assess_id) . '">Take Assessment</pr360-questionnaire>';
    $output .= '</div>';
    $output .= '</div></div></div>';
    return $output;
}

// Render callback for Two Column block
function global360blocks_render_two_column_block( $attributes, $content ) {
    // Get Assessment ID from theme settings (360_global_settings array)
    $global_settings = get_option('360_global_settings', []);
    $assess_id = isset($global_settings['assessment_id']) ? $global_settings['assessment_id'] : '';
    
    $image_url = !empty($attributes['imageUrl']) ? esc_url($attributes['imageUrl']) : '';
    $heading = !empty($attributes['heading']) ? wp_kses_post($attributes['heading']) : '';
    $body_text = !empty($attributes['bodyText']) ? wp_kses_post($attributes['bodyText']) : '';
    
    $output = '<div class="two-column-block">';
    $output .= '<div class="two-column-container">';
    
    // Left column - Image
    $output .= '<div class="two-column-image">';
    if ($image_url) {
        $output .= '<img src="' . $image_url . '" alt="" class="column-image" />';
    }
    $output .= '</div>';
    
    // Right column - Content
    $output .= '<div class="two-column-content">';
    if ($heading) {
        $output .= '<h2 class="two-column-heading">' . $heading . '</h2>';
    }
    if ($body_text) {
        $output .= '<div class="two-column-body">' . $body_text . '</div>';
    }
    $output .= '<div class="two-column-button">';
    $output .= '<pr360-questionnaire class="btn btn_global" url="wss://app.patientreach360.com/socket" site-id="' . esc_attr($assess_id) . '">Take Assessment</pr360-questionnaire>';
    $output .= '</div>';
    $output .= '</div>';
    
    $output .= '</div></div>';
    return $output;
}

// Render callback for Full Page Hero block
function global360blocks_render_full_hero_block( $attributes, $content ) {
    // Get Assessment ID from theme settings (360_global_settings array)
    $global_settings = get_option('360_global_settings', []);
    $assess_id = isset($global_settings['assessment_id']) ? $global_settings['assessment_id'] : '';
    
    $image_url = !empty($attributes['bgImageUrl']) ? esc_url($attributes['bgImageUrl']) : '';
    $heading = !empty($attributes['heading']) ? wp_kses_post($attributes['heading']) : '';
    $subheading = !empty($attributes['subheading']) ? wp_kses_post($attributes['subheading']) : '';
    $output = '<div class="full-hero-block" style="background-image: url(' . $image_url . ');">';
    $output .= '<div class="full-hero-content">';
    if ($heading) {
        $output .= '<h1 class="full-hero-heading">' . $heading . '</h1>';
    }
    if ($subheading) {
        $output .= '<p class="full-hero-subheading">' . $subheading . '</p>';
    }
    $output .= '<pr360-questionnaire class="btn btn_global" url="wss://app.patientreach360.com/socket" site-id="' . esc_attr($assess_id) . '">Take Risk Assessment Now</pr360-questionnaire>';
    $output .= '</div></div>';
    return $output;
}

// Render callback for Simple Hero block
function global360blocks_render_simple_hero_block( $attributes, $content ) {
    $page_title = get_the_title();
    
    $output = '<div class="wp-block-global360blocks-simple-hero">';
    $output .= '<div class="simple-hero-content">';
    $output .= '<h1>' . esc_html($page_title) . '</h1>';
    $output .= '</div>';
    $output .= '</div>';
    
    return $output;
}

add_action( 'init', 'global360blocks_register_blocks' );

// Enqueue block CSS for frontend
add_action('wp_enqueue_scripts', function() {
    // Simple Hero block CSS
    if (file_exists( plugin_dir_path( __FILE__ ) . 'blocks/simple-hero/build/style-index.css' )) {
        wp_enqueue_style(
            'global360blocks-simple-hero-style-frontend',
            plugins_url('blocks/simple-hero/build/style-index.css', __FILE__),
            array(),
            filemtime(plugin_dir_path(__FILE__) . 'blocks/simple-hero/build/style-index.css')
        );
    }
    
    // Full Hero block CSS
    if (file_exists( plugin_dir_path( __FILE__ ) . 'blocks/full-hero/build/style-index.css' )) {
        wp_enqueue_style(
            'global360blocks-full-hero-style-frontend',
            plugins_url('blocks/full-hero/build/style-index.css', __FILE__),
            array(),
            filemtime(plugin_dir_path(__FILE__) . 'blocks/full-hero/build/style-index.css')
        );
    }
    
    // CTA block CSS
    if (file_exists( plugin_dir_path( __FILE__ ) . 'blocks/cta/build/style-index.css' )) {
        wp_enqueue_style(
            'global360blocks-cta-style-frontend',
            plugins_url('blocks/cta/build/style-index.css', __FILE__),
            array(),
            filemtime(plugin_dir_path(__FILE__) . 'blocks/cta/build/style-index.css')
        );
    }
    
    // Two Column block CSS
    if (file_exists( plugin_dir_path( __FILE__ ) . 'blocks/two-column/build/style-index.css' )) {
        wp_enqueue_style(
            'global360blocks-two-column-style-frontend',
            plugins_url('blocks/two-column/build/style-index.css', __FILE__),
            array(),
            filemtime(plugin_dir_path(__FILE__) . 'blocks/two-column/build/style-index.css')
        );
    }
    
    // Video Two Column block CSS
    if (file_exists( plugin_dir_path( __FILE__ ) . 'blocks/video-two-column/build/style-index.css' )) {
        wp_enqueue_style(
            'global360blocks-video-two-column-style-frontend',
            plugins_url('blocks/video-two-column/build/style-index.css', __FILE__),
            array(),
            filemtime(plugin_dir_path(__FILE__) . 'blocks/video-two-column/build/style-index.css')
        );
    }
    
    // Find Doctor block frontend CSS
    if (file_exists( plugin_dir_path( __FILE__ ) . 'blocks/find-doctor/build/style-index.css' )) {
        wp_enqueue_style(
            'find-doctor-block-css',
            plugins_url('blocks/find-doctor/build/style-index.css', __FILE__),
            array(),
            filemtime(plugin_dir_path(__FILE__) . 'blocks/find-doctor/build/style-index.css')
        );
    }
    
    // Latest Articles block frontend CSS
    if (file_exists( plugin_dir_path( __FILE__ ) . 'blocks/latest-articles/build/style-index.css' )) {
        wp_enqueue_style(
            'latest-articles-block-css',
            plugins_url('blocks/latest-articles/build/style-index.css', __FILE__),
            array(),
            filemtime(plugin_dir_path(__FILE__) . 'blocks/latest-articles/build/style-index.css')
        );
    }
    
    // Medical Icons block frontend CSS - commented out until built
    // if (file_exists( plugin_dir_path( __FILE__ ) . 'blocks/medical-icons/build/style-index.css' )) {
    //     wp_enqueue_style(
    //         'medical-icons-block-css',
    //         plugins_url( 'blocks/medical-icons/build/style-index.css', __FILE__ ),
    //         array(),
    //         filemtime(plugin_dir_path(__FILE__) . 'blocks/medical-icons/build/style-index.css')
    //     );
    // }
    
    // Info Cards block frontend CSS
    if (file_exists( plugin_dir_path( __FILE__ ) . 'blocks/info-cards/build/style-index.css' )) {
        wp_enqueue_style(
            'info-cards-block-css',
            plugins_url( 'blocks/info-cards/build/style-index.css', __FILE__ ),
            array(),
            filemtime(plugin_dir_path(__FILE__) . 'blocks/info-cards/build/style-index.css')
        );
    }
    
    // Popular Practices block frontend CSS
    if (file_exists( plugin_dir_path( __FILE__ ) . 'blocks/popular-practices/build/style-index.css' )) {
        wp_enqueue_style(
            'popular-practices-block-css',
            plugins_url( 'blocks/popular-practices/build/style-index.css', __FILE__ ),
            array(),
            filemtime(plugin_dir_path(__FILE__) . 'blocks/popular-practices/build/style-index.css')
        );
    }
    
    // Two Column Slider block frontend CSS
    if (file_exists( plugin_dir_path( __FILE__ ) . 'blocks/two-column-slider/build/style-index.css' )) {
        wp_enqueue_style(
            'two-column-slider-block-css',
            plugins_url( 'blocks/two-column-slider/build/style-index.css', __FILE__ ),
            array(),
            filemtime(plugin_dir_path(__FILE__) . 'blocks/two-column-slider/build/style-index.css')
        );
    }
    
    // Info Cards block frontend JavaScript
    if (file_exists( plugin_dir_path( __FILE__ ) . 'blocks/info-cards/build/index.js' )) {
        wp_enqueue_script(
            'info-cards-block-frontend',
            plugins_url( 'blocks/info-cards/build/index.js', __FILE__ ),
            array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components'),
            filemtime(plugin_dir_path(__FILE__) . 'blocks/info-cards/build/index.js'),
            true
        );
    }
    
    // Two Column Slider block frontend JavaScript
    if (file_exists( plugin_dir_path( __FILE__ ) . 'blocks/two-column-slider/build/index.js' )) {
        wp_enqueue_script(
            'two-column-slider-block-frontend',
            plugins_url( 'blocks/two-column-slider/build/index.js', __FILE__ ),
            array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components'),
            filemtime(plugin_dir_path(__FILE__) . 'blocks/two-column-slider/build/index.js'),
            true
        );
    }
});

?>

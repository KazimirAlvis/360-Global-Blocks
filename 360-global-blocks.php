<?php
/*
Plugin Name: 360 Global Blocks
Description: Custom Gutenberg blocks for the 360 network. 
 * Version: 1.3.0
Author: Kaz Alvis
*/

if ( ! defined( 'ABSPATH' ) ) exit;

// WordPress Plugin Update Checker - Proper Implementation
if( ! class_exists( 'SB_Global_Blocks_Update_Checker' ) ) {
    class SB_Global_Blocks_Update_Checker {
        public $plugin_slug;
        public $plugin_file;
        public $version;
        public $cache_key;
        public $cache_allowed;
        public $github_username;
        public $github_repo;

        public function __construct() {
            $this->plugin_slug = plugin_basename( __DIR__ );
            $this->plugin_file = plugin_basename( __FILE__ );
            $this->version = '1.3.0';
            $this->cache_key = 'sb_global_blocks_update_checker';
            $this->cache_allowed = true;
            $this->github_username = 'Superkore-Media';
            $this->github_repo = '360-Global-Blocks';

            add_filter( 'plugins_api', array( $this, 'info' ), 20, 3 );
            add_filter( 'site_transient_update_plugins', array( $this, 'update' ) );
            add_action( 'upgrader_process_complete', array( $this, 'purge' ), 10, 2 );
        }

        public function request() {
            $remote = get_transient( $this->cache_key );

            if( false === $remote || ! $this->cache_allowed ) {
                // Get plugin info from GitHub
                $api_url = "https://api.github.com/repos/{$this->github_username}/{$this->github_repo}/contents/360-global-blocks.php";
                
                $response = wp_remote_get($api_url, array(
                    'headers' => array(
                        'User-Agent' => 'WordPress/' . get_bloginfo('version') . '; ' . home_url(),
                        'Accept' => 'application/vnd.github.v3+json'
                    ),
                    'timeout' => 15
                ));

                if( 
                    is_wp_error( $response )
                    || 200 !== wp_remote_retrieve_response_code( $response )
                    || empty( wp_remote_retrieve_body( $response ) )
                ) {
                    return false;
                }

                $body = wp_remote_retrieve_body( $response );
                $data = json_decode( $body, true );
                
                if( ! isset( $data['content'] ) ) {
                    return false;
                }
                
                // Decode the plugin file content
                $content = base64_decode( $data['content'] );
                
                // Extract version from plugin header
                $github_version = '1.3.0'; // fallback
                if( preg_match( '/Version:\s*(.+)/', $content, $matches ) ) {
                    $github_version = trim( $matches[1] );
                }
                
                // Extract description
                $description = 'Custom Gutenberg blocks for the 360 network.';
                if( preg_match( '/Description:\s*(.+)/', $content, $matches ) ) {
                    $description = trim( $matches[1] );
                }

                // Create update info object
                $remote = (object) array(
                    'name' => '360 Global Blocks',
                    'slug' => $this->plugin_slug,
                    'author' => '<a href="https://360.health">360 Health</a>',
                    'author_profile' => 'https://360.health',
                    'version' => $github_version,
                    'download_url' => "https://github.com/{$this->github_username}/{$this->github_repo}/archive/refs/heads/main.zip",
                    'requires' => '6.0',
                    'tested' => get_bloginfo('version'),
                    'requires_php' => '7.4',
                    'last_updated' => date('Y-m-d H:i:s'),
                    'sections' => array(
                        'description' => $description,
                        'installation' => 'Upload the plugin files to the `/wp-content/plugins/360-global-blocks` directory, or install the plugin through the WordPress plugins screen directly.',
                        'changelog' => '<h4>1.3.0</h4><ul><li>Implemented proper WordPress plugin update system</li><li>Added comprehensive Health Icons collection</li><li>Fixed plugin update notifications</li></ul>'
                    ),
                    'banners' => array(
                        'low' => '',
                        'high' => ''
                    )
                );

                if( $this->cache_allowed ) {
                    set_transient( $this->cache_key, $remote, DAY_IN_SECONDS );
                }
            }

            return $remote;
        }

        public function info( $res, $action, $args ) {
            // do nothing if this is not about getting plugin information
            if( 'plugin_information' !== $action ) {
                return $res;
            }

            // do nothing if it is not our plugin
            if( $this->plugin_slug !== $args->slug ) {
                return $res;
            }

            // get updates
            $remote = $this->request();

            if( ! $remote ) {
                return $res;
            }

            $res = new stdClass();
            $res->name = $remote->name;
            $res->slug = $remote->slug;
            $res->author = $remote->author;
            $res->author_profile = $remote->author_profile;
            $res->version = $remote->version;
            $res->tested = $remote->tested;
            $res->requires = $remote->requires;
            $res->requires_php = $remote->requires_php;
            $res->download_link = $remote->download_url;
            $res->trunk = $remote->download_url;
            $res->last_updated = $remote->last_updated;
            $res->sections = $remote->sections;
            $res->banners = $remote->banners;

            return $res;
        }

        public function update( $transient ) {
            if ( empty($transient->checked ) ) {
                return $transient;
            }

            // get update info
            $remote = $this->request();

            if( 
                $remote
                && version_compare( $this->version, $remote->version, '<' )
                && version_compare( $remote->requires, get_bloginfo( 'version' ), '<=' )
                && version_compare( $remote->requires_php, PHP_VERSION, '<=' )
            ) {
                $res = new stdClass();
                $res->slug = $remote->slug;
                $res->plugin = $this->plugin_file;
                $res->new_version = $remote->version;
                $res->tested = $remote->tested;
                $res->package = $remote->download_url;
                
                $transient->response[ $res->plugin ] = $res;
            }

            return $transient;
        }

        public function purge( $upgrader, $options ){
            if (
                $this->cache_allowed
                && 'update' === $options['action']
                && 'plugin' === $options[ 'type' ]
            ) {
                // clean the cache when new plugin version is installed
                delete_transient( $this->cache_key );
            }
        }
    }

    new SB_Global_Blocks_Update_Checker();
}

// Include Health Icons Loader
require_once plugin_dir_path(__FILE__) . 'inc/health-icons-loader.php';

// Health Icons AJAX Handlers
add_action('wp_ajax_get_health_icon', 'handle_get_health_icon_ajax');
add_action('wp_ajax_nopriv_get_health_icon', 'handle_get_health_icon_ajax');

function handle_get_health_icon_ajax() {
    check_ajax_referer('health_icons_nonce', 'nonce');
    
    $icon_key = sanitize_text_field($_POST['icon_key']);
    
    if (empty($icon_key)) {
        wp_die();
    }
    
    $loader = HealthIconsLoader::getInstance();
    $svg_content = $loader->getIcon($icon_key);
    
    if ($svg_content) {
        wp_send_json_success($svg_content);
    } else {
        wp_send_json_error('Icon not found');
    }
}

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
        $output .= '<pr360-questionnaire url="wss://app.patientreach360.com/socket" site-id="' . esc_attr($assess_id) . '">Take Assessment</pr360-questionnaire>';
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



// Info Cards block now uses render.php file - old hardcoded function removed

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
    

    
    // Health Icons JavaScript utility
    wp_enqueue_script(
        'health-icons-js',
        plugins_url( 'assets/js/health-icons.js', __FILE__ ),
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'assets/js/health-icons.js')
    );
    
    // Localize Health Icons data - Temporarily disabled
    /*
    $loader = HealthIconsLoader::getInstance();
    $all_icons = $loader->getAllIcons();
    
    wp_localize_script('health-icons-js', 'healthIconsAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('health_icons_nonce'),
        'all_icons' => $all_icons
    ));
    */
    
    // Info Cards block JavaScript
    if (file_exists( plugin_dir_path( __FILE__ ) . 'blocks/info-cards/build/index.js' )) {
        // Load asset file for dependencies
        $asset_file = include( plugin_dir_path( __FILE__ ) . 'blocks/info-cards/build/index.asset.php');
        
        $dependencies = array_merge($asset_file['dependencies'], array('health-icons-js'));
        
        wp_enqueue_script(
            'info-cards-block-editor',
            plugins_url( 'blocks/info-cards/build/index.js', __FILE__ ),
            $dependencies,
            $asset_file['version']
        );
        
        // Localize Health Icons data for info-cards block
        $health_icons_loader = HealthIconsLoader::getInstance();
        $all_icons = $health_icons_loader->getAllIcons();
        
        wp_localize_script('info-cards-block-editor', 'healthIconsData', $all_icons);
        
        wp_localize_script('info-cards-block-editor', 'healthIconsAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('health_icons_nonce')
        ));
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

// REST API endpoint for Symptoms AI content generation
add_action('rest_api_init', function() {
    register_rest_route('360blocks/v1', '/generate-symptoms', array(
        'methods' => 'POST',
        'callback' => 'global360blocks_generate_symptoms_api',
        'permission_callback' => function() {
            return current_user_can('edit_posts');
        }
    ));
    
    // Alternative route for symptoms AI block
    register_rest_route('global360blocks/v1', '/generate-symptoms-content', array(
        'methods' => 'POST',
        'callback' => 'global360blocks_generate_symptoms_api',
        'permission_callback' => function() {
            return current_user_can('edit_posts');
        }
    ));
});

function global360blocks_generate_symptoms_api($request) {
    $symptom = sanitize_text_field($request->get_param('symptom'));
    
    if (empty($symptom)) {
        return new WP_Error('missing_symptom', 'Symptom parameter is required', array('status' => 400));
    }
    
    // Check cache first
    $cache_key = 'symptoms_ai_final_clean_' . md5($symptom);
    $cached_content = get_transient($cache_key);
    
    if ($cached_content !== false) {
        return array(
            'success' => true,
            'content' => $cached_content,
            'source' => 'cache'
        );
    }
    
    // Generate content using template system
    $content = global360blocks_generate_symptoms_content($symptom);
    
    // Cache for 7 days
    set_transient($cache_key, $content, 7 * DAY_IN_SECONDS);
    
    return array(
        'success' => true,
        'content' => $content,
        'source' => 'generated'
    );
}

// Include symptoms AI render functions
require_once plugin_dir_path(__FILE__) . 'blocks/symptoms-ai/render.php';

// Include page title hero render functions
require_once plugin_dir_path(__FILE__) . 'blocks/page-title-hero/render.php';

// Block category is already registered above - removed duplicate

// Register block
function global360blocks_register_blocks() {
    // Register Simple Hero block
    register_block_type( __DIR__ . '/blocks/simple-hero/build', array(
        'render_callback' => 'global360blocks_render_simple_hero_block',
    ));
    
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

    // Register Info Cards block - explicit render callback
    register_block_type( __DIR__ . '/blocks/info-cards/build', array(
        'render_callback' => function($attributes) {
            ob_start();
            include __DIR__ . '/blocks/info-cards/build/render.php';
            return ob_get_clean();
        }
    ));
    
    register_block_type( __DIR__ . '/blocks/popular-practices/build', array(
        'render_callback' => 'global360blocks_render_popular_practices_block',
    ));
    
    // Register Two Column Slider block
    register_block_type( __DIR__ . '/blocks/two-column-slider/build', array(
        'render_callback' => 'global360blocks_render_two_column_slider_block',
    ));
    
    // Register Symptoms AI block
    register_block_type( __DIR__ . '/blocks/symptoms-ai/build', array(
        'render_callback' => 'global360blocks_render_symptoms_ai_block'
    ));
    
    // Register Page Title Hero block
    register_block_type( __DIR__ . '/blocks/page-title-hero/build', array(
        'render_callback' => 'global360blocks_render_page_title_hero_block'
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
    $output .= '<pr360-questionnaire url="wss://app.patientreach360.com/socket" site-id="' . esc_attr($assess_id) . '">Take Assessment</pr360-questionnaire>';
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
    $output .= '<pr360-questionnaire url="wss://app.patientreach360.com/socket" site-id="' . esc_attr($assess_id) . '">Take Assessment</pr360-questionnaire>';
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
    $output .= '<pr360-questionnaire url="wss://app.patientreach360.com/socket" site-id="' . esc_attr($assess_id) . '">Take Risk Assessment Now</pr360-questionnaire>';
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

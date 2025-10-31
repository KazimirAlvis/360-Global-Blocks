<?php
/*
Plugin Name: 360 Global Blocks
Description: Custom Gutenberg blocks for the 360 network. 
 * Version: 1.3.24
Author: Kaz Alvis
*/

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'SB_GLOBAL_BLOCKS_VERSION', '1.3.24' );
define( 'SB_GLOBAL_BLOCKS_PLUGIN_FILE', __FILE__ );
define(
    'SB_GLOBAL_BLOCKS_MANIFEST_URL',
    'https://raw.githubusercontent.com/KazimirAlvis/360-Global-Blocks/main/plugin-manifest.json'
);

require_once plugin_dir_path( __FILE__ ) . 'inc/class-sb-global-blocks-updater.php';

/**
 * Resolve the font family currently assigned to heading typography.
 *
 * @return string Heading font family string if identifiable, otherwise empty string.
 */
function global360blocks_get_heading_font_family() {
    static $resolved = null;

    if ( null !== $resolved ) {
        return $resolved;
    }

    $font_family = '';

    if ( function_exists( 'wp_get_global_styles' ) ) {
        $maybe_heading_font = wp_get_global_styles( array( 'elements', 'heading', 'typography', 'fontFamily' ) );
        if ( is_string( $maybe_heading_font ) && '' !== $maybe_heading_font ) {
            $font_family = $maybe_heading_font;
        }
    }

    if ( ! $font_family && function_exists( 'wp_get_global_settings' ) ) {
        $maybe_user_heading = wp_get_global_settings( array( 'typography', 'fontFamilies', 'user', 'heading', 'fontFamily' ) );
        if ( is_string( $maybe_user_heading ) && '' !== $maybe_user_heading ) {
            $font_family = $maybe_user_heading;
        }
    }

    if ( ! $font_family && function_exists( 'wp_get_global_settings' ) ) {
        $maybe_theme_heading = wp_get_global_settings( array( 'typography', 'fontFamilies', 'theme', 'heading', 'fontFamily' ) );
        if ( is_string( $maybe_theme_heading ) && '' !== $maybe_theme_heading ) {
            $font_family = $maybe_theme_heading;
        }
    }

    if ( ! $font_family && function_exists( 'wp_get_global_settings' ) ) {
        $maybe_root_font = wp_get_global_settings( array( 'typography', 'fontFamily' ) );
        if ( is_string( $maybe_root_font ) && '' !== $maybe_root_font ) {
            $font_family = $maybe_root_font;
        }
    }

    if ( ! $font_family ) {
        $maybe_theme_mod = get_theme_mod( 'typography_heading_font_family', '' );
        if ( is_string( $maybe_theme_mod ) ) {
            $font_family = $maybe_theme_mod;
        }
    }

    if ( is_string( $font_family ) && preg_match( '/var\(([^)]+)\)/', $font_family, $matches ) ) {
        $preset_identifier = trim( $matches[1] );

        if ( '' !== $preset_identifier && function_exists( 'wp_get_global_settings' ) ) {
            $preset_identifier = explode( ',', $preset_identifier )[0];
            $preset_identifier = trim( $preset_identifier );

            if ( 0 === strpos( $preset_identifier, '--wp--preset--font-family--' ) ) {
                $slug = str_replace( '--wp--preset--font-family--', '', $preset_identifier );
                $font_collections = wp_get_global_settings( array( 'typography', 'fontFamilies' ) );

                if ( is_array( $font_collections ) ) {
                    foreach ( $font_collections as $collection ) {
                        if ( ! is_array( $collection ) ) {
                            continue;
                        }

                        foreach ( $collection as $font_entry ) {
                            if ( isset( $font_entry['slug'], $font_entry['fontFamily'] ) && $slug === $font_entry['slug'] ) {
                                $font_family = $font_entry['fontFamily'];
                                break 2;
                            }
                        }
                    }
                }
            }
        }
    }

    $resolved = is_string( $font_family ) ? $font_family : '';

    return $resolved;
}

/**
 * Determine the letter-spacing value for the heading font.
 *
 * Defaults to normal spacing unless the heading font resolves to Anton, in which case
 * the site uses the requested 0.5px spacing.
 *
 * @return string CSS letter-spacing value.
 */
function global360blocks_get_heading_letter_spacing_value() {
    static $cached = null;

    if ( null !== $cached ) {
        return $cached;
    }

    $font_family = strtolower( global360blocks_get_heading_font_family() );
    $is_anton = false;

    if ( $font_family ) {
    if ( false !== strpos( $font_family, 'anton' ) || false !== strpos( $font_family, 'wp--preset--font-family--anton' ) ) {
            $is_anton = true;
        }
    }

    $value = $is_anton ? '0.5px' : 'normal';

    $cached = apply_filters( 'global360blocks_heading_letter_spacing_value', $value, $font_family );

    return $cached;
}

/**
 * Build shared CSS for heading letter-spacing support.
 *
 * @param string $context Either 'frontend' or 'editor'.
 * @return string
 */
function global360blocks_get_heading_letter_spacing_css( $context = 'frontend' ) {
    $letter_spacing = global360blocks_get_heading_letter_spacing_value();

    if ( ! $letter_spacing ) {
        return '';
    }

    $root_selector = ':root{--heading-letter-spacing:' . esc_attr( $letter_spacing ) . ';}';
    $heading_selector = ':where(h1,h2,h3,h4,h5,h6){letter-spacing:var(--heading-letter-spacing,normal);}';

    if ( 'editor' === $context ) {
        $heading_selector = '.editor-styles-wrapper ' . $heading_selector;
    }

    return $root_selector . $heading_selector;
}

/**
 * Enqueue heading letter-spacing support on the frontend.
 */
function global360blocks_enqueue_heading_letter_spacing_styles() {
    $css = global360blocks_get_heading_letter_spacing_css( 'frontend' );

    if ( '' === $css ) {
        return;
    }

    $handle = 'global360blocks-heading-typography';

    if ( ! wp_style_is( $handle, 'enqueued' ) ) {
        wp_register_style( $handle, false, array(), SB_GLOBAL_BLOCKS_VERSION );
        wp_enqueue_style( $handle );
    }

    wp_add_inline_style( $handle, $css );
}
add_action( 'wp_enqueue_scripts', 'global360blocks_enqueue_heading_letter_spacing_styles', 1 );

/**
 * Enqueue heading letter-spacing support for the block editor.
 */
function global360blocks_enqueue_heading_letter_spacing_editor_styles() {
    $css = global360blocks_get_heading_letter_spacing_css( 'editor' );

    if ( '' === $css ) {
        return;
    }

    $handle = 'global360blocks-heading-typography-editor';

    if ( ! wp_style_is( $handle, 'enqueued' ) ) {
        wp_register_style( $handle, false, array(), SB_GLOBAL_BLOCKS_VERSION );
        wp_enqueue_style( $handle );
    }

    wp_add_inline_style( $handle, $css );
}
add_action( 'enqueue_block_editor_assets', 'global360blocks_enqueue_heading_letter_spacing_editor_styles', 1 );

function sb_global_blocks_bootstrap_updater() {
    if ( isset( $GLOBALS['sb_global_blocks_updater'] ) ) {
        return;
    }

    $GLOBALS['sb_global_blocks_updater'] = new SB_Global_Blocks_Updater(
        array(
            'manifest_url' => SB_GLOBAL_BLOCKS_MANIFEST_URL,
            'plugin_file'  => SB_GLOBAL_BLOCKS_PLUGIN_FILE,
            'version'      => SB_GLOBAL_BLOCKS_VERSION,
        )
    );
}
add_action( 'plugins_loaded', 'sb_global_blocks_bootstrap_updater', 5 );

function sb_global_blocks_rename_github_package( $source, $remote_source, $upgrader, $hook_extra ) {
    $source_path  = untrailingslashit( $source );
    $source_dir   = basename( $source_path );
    $expected_dir = '360-global-blocks';

    $is_target = false;

    if ( isset( $hook_extra['plugin'] ) && strcasecmp( $hook_extra['plugin'], '360-Global-Blocks/360-global-blocks.php' ) === 0 ) {
        $is_target = true;
    }

    if ( isset( $hook_extra['slug'] ) && '360-global-blocks' === $hook_extra['slug'] ) {
        $is_target = true;
    }

    if ( ! $is_target && false !== stripos( $source_dir, '360-global-blocks' ) ) {
        $is_target = true;
    }

    if ( ! $is_target ) {
        return $source;
    }

    $desired_path = trailingslashit( dirname( $source_path ) ) . $expected_dir;

    if ( strcasecmp( $source_dir, $expected_dir ) === 0 ) {
        return trailingslashit( $source_path );
    }

    global $wp_filesystem;

    if ( ! $wp_filesystem && defined( 'ABSPATH' ) ) {
        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        WP_Filesystem();
    }

    if ( $wp_filesystem && $wp_filesystem->exists( $desired_path ) ) {
        $wp_filesystem->delete( $desired_path, true );
    }

    $moved = false;

    if ( $wp_filesystem && $wp_filesystem->move( trailingslashit( $source_path ), trailingslashit( $desired_path ), true ) ) {
        $moved = true;
    }

    if ( ! $moved && @rename( $source_path, $desired_path ) ) {
        $moved = true;
    }

    if ( $moved ) {
        return trailingslashit( $desired_path );
    }

    return $source;
}
add_filter( 'upgrader_source_selection', 'sb_global_blocks_rename_github_package', 10, 4 );

function sb_global_blocks_ensure_install_location( $response, $hook_extra, $result ) {
    $expected_dir = '360-global-blocks';
    $is_target    = false;

    if ( isset( $hook_extra['plugin'] ) && strcasecmp( $hook_extra['plugin'], '360-Global-Blocks/360-global-blocks.php' ) === 0 ) {
        $is_target = true;
    }

    if ( isset( $hook_extra['slug'] ) && '360-global-blocks' === $hook_extra['slug'] ) {
        $is_target = true;
    }

    if ( ! $is_target ) {
        return $response;
    }

    $destination = isset( $result['destination'] ) ? $result['destination'] : '';
    if ( ! $destination ) {
        return $response;
    }

    $destination_dir = trailingslashit( $destination );
    $expected_path   = trailingslashit( WP_PLUGIN_DIR ) . $expected_dir . '/';

    if ( strtolower( $destination_dir ) === strtolower( $expected_path ) ) {
        return $response;
    }

    global $wp_filesystem;

    if ( ! $wp_filesystem && defined( 'ABSPATH' ) ) {
        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        WP_Filesystem();
    }

    $moved = false;

    if ( $wp_filesystem && $wp_filesystem->exists( $destination_dir ) ) {
        if ( $wp_filesystem->exists( $expected_path ) ) {
            $wp_filesystem->delete( $expected_path, true );
        }

        if ( $wp_filesystem->move( $destination_dir, $expected_path, true ) ) {
            $moved = true;
        }
    }

    if ( ! $moved && is_dir( $destination_dir ) ) {
        if ( is_dir( $expected_path ) ) {
            sb_global_blocks_rrmdir( $expected_path );
        }

        if ( @rename( untrailingslashit( $destination_dir ), untrailingslashit( $expected_path ) ) ) {
            $moved = true;
        }
    }

    if ( $moved ) {
        $result['destination'] = $expected_path;
        return $result;
    }

    return $response;
}
add_filter( 'upgrader_post_install', 'sb_global_blocks_ensure_install_location', 10, 3 );

if ( ! function_exists( 'sb_global_blocks_rrmdir' ) ) {
    function sb_global_blocks_rrmdir( $dir ) {
        if ( ! is_dir( $dir ) ) {
            return;
        }

        $items = scandir( $dir );
        if ( ! $items ) {
            return;
        }

        foreach ( $items as $item ) {
            if ( '.' === $item || '..' === $item ) {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if ( is_dir( $path ) ) {
                sb_global_blocks_rrmdir( $path );
            } else {
                @unlink( $path );
            }
        }

        @rmdir( $dir );
    }
}

function sb_global_blocks_get_update_debug_data() {
    $updater = isset( $GLOBALS['sb_global_blocks_updater'] ) ? $GLOBALS['sb_global_blocks_updater'] : null;

    if ( ! $updater instanceof SB_Global_Blocks_Updater ) {
        return array();
    }

    $remote = $updater->request();
    $transient  = get_site_transient( 'update_plugins' );
    $plugin_key = plugin_basename( SB_GLOBAL_BLOCKS_PLUGIN_FILE );
    $update_row = ( $transient && isset( $transient->response[ $plugin_key ] ) ) ? $transient->response[ $plugin_key ] : null;

    return array(
        'installed_version'       => $updater->get_version(),
        'remote_version'          => $remote ? ( isset( $remote->version ) ? $remote->version : 'n/a' ) : 'n/a',
        'remote_requires_wp'      => $remote && isset( $remote->requires ) ? $remote->requires : 'n/a',
        'remote_requires_php'     => $remote && isset( $remote->requires_php ) ? $remote->requires_php : 'n/a',
        'download_url'            => $remote && isset( $remote->download_url ) ? $remote->download_url : 'n/a',
        'remote_last_updated'     => $remote && isset( $remote->last_updated ) ? $remote->last_updated : 'n/a',
        'transient_detected'      => $update_row ? $update_row->new_version : 'n/a',
        'manifest_url'            => SB_GLOBAL_BLOCKS_MANIFEST_URL,
        'last_error'              => $updater->get_last_error() ? $updater->get_last_error() : 'none',
    );
}

function sb_global_blocks_update_debug_notice() {
    if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
        return;
    }

    if ( ! isset( $_GET['sb-update-debug'] ) ) {
        return;
    }

    $data = sb_global_blocks_get_update_debug_data();
    if ( empty( $data ) ) {
        return;
    }

    $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
    if ( ! $screen || ! in_array( $screen->id, array( 'plugins', 'update-core' ), true ) ) {
        return;
    }

    echo '<div class="notice notice-info"><p><strong>360 Global Blocks Update Debug</strong></p><ul style="margin-left:20px;">';
    foreach ( $data as $label => $value ) {
        $display = is_scalar( $value ) ? $value : wp_json_encode( $value );
        echo '<li><strong>' . esc_html( ucwords( str_replace( '_', ' ', $label ) ) ) . ':</strong> ' . esc_html( $display ) . '</li>';
    }
    echo '</ul></div>';
}
add_action( 'admin_notices', 'sb_global_blocks_update_debug_notice' );

function sb_global_blocks_add_update_tools_page() {
    add_management_page(
        __( '360 Blocks Updates', '360-global-blocks' ),
        __( '360 Blocks Updates', '360-global-blocks' ),
        'manage_options',
        '360-blocks-updates',
        'sb_global_blocks_render_update_tools_page'
    );
}
add_action( 'admin_menu', 'sb_global_blocks_add_update_tools_page' );

function sb_global_blocks_render_update_tools_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'Sorry, you are not allowed to access this page.', '360-global-blocks' ) );
    }

    echo '<div class="wrap"><h1>' . esc_html__( '360 Global Blocks - Update Diagnostics', '360-global-blocks' ) . '</h1>';

    $data = sb_global_blocks_get_update_debug_data();
    if ( empty( $data ) ) {
        echo '<p>' . esc_html__( 'Updater service is not initialised.', '360-global-blocks' ) . '</p></div>';
        return;
    }

    $transient  = get_site_transient( 'update_plugins' );
    $plugin_key = plugin_basename( __FILE__ );
    $update_row = ( $transient && isset( $transient->response[ $plugin_key ] ) ) ? $transient->response[ $plugin_key ] : null;

    echo '<table class="widefat striped" style="max-width:680px">';
    foreach ( $data as $label => $value ) {
        $display = is_scalar( $value ) ? $value : wp_json_encode( $value );
        echo '<tr><th scope="row">' . esc_html( ucwords( str_replace( '_', ' ', $label ) ) ) . '</th><td>' . esc_html( $display ) . '</td></tr>';
    }

    if ( $update_row ) {
        echo '<tr><th scope="row">' . esc_html__( 'Update detected', '360-global-blocks' ) . '</th><td>' . esc_html( $update_row->new_version ) . '</td></tr>';
    } else {
        echo '<tr><th scope="row">' . esc_html__( 'Update detected', '360-global-blocks' ) . '</th><td>' . esc_html__( 'No entry present in update_plugins transient.', '360-global-blocks' ) . '</td></tr>';
    }
    echo '</table>';

    echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="margin-top:20px;">';
    wp_nonce_field( 'sb_global_blocks_force_check' );
    echo '<input type="hidden" name="action" value="sb_global_blocks_force_check" />';
    echo '<input type="submit" class="button button-primary" value="' . esc_attr__( 'Force Update Check Now', '360-global-blocks' ) . '" />';
    echo '</form>';

    echo '<p style="margin-top:15px;">' . esc_html__( 'Tip: after forcing a check, revisit the Plugins screen or click “Check again” on Dashboard → Updates.', '360-global-blocks' ) . '</p>';

    echo '</div>';
}

function sb_global_blocks_handle_force_check() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'Sorry, you are not allowed to perform this action.', '360-global-blocks' ) );
    }

    check_admin_referer( 'sb_global_blocks_force_check' );

    if ( isset( $GLOBALS['sb_global_blocks_updater'] ) && $GLOBALS['sb_global_blocks_updater'] instanceof SB_Global_Blocks_Updater ) {
        $GLOBALS['sb_global_blocks_updater']->force_check();
    }

    wp_safe_redirect( add_query_arg( array( 'page' => '360-blocks-updates', 'status' => 'forced' ), admin_url( 'tools.php' ) ) );
    exit;
}
add_action( 'admin_post_sb_global_blocks_force_check', 'sb_global_blocks_handle_force_check' );

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
    
    $cards = array();
    $random_pool = !empty($clinic_pages) ? array_values($clinic_pages) : array();

    foreach ($clinics as $index => $clinic) {
        $clinic_id   = !empty($clinic['clinicId']) ? intval($clinic['clinicId']) : 0;
        $custom_name = !empty($clinic['customName']) ? sanitize_text_field($clinic['customName']) : '';
        $custom_logo = !empty($clinic['customLogo']) ? esc_url_raw($clinic['customLogo']) : '';
        $custom_url  = !empty($clinic['customUrl']) ? esc_url_raw($clinic['customUrl']) : '';

        $card = null;

        if ($clinic_id) {
            $clinic_page = get_post($clinic_id);
            if ($clinic_page instanceof WP_Post) {
                $clinic_name = $custom_name ?: get_the_title($clinic_page);
                $clinic_url  = $custom_url ?: get_permalink($clinic_page);

                if ($custom_logo) {
                    $clinic_logo_url = $custom_logo;
                } elseif (function_exists('cpt360_get_clinic_logo_url')) {
                    $clinic_logo_url = cpt360_get_clinic_logo_url($clinic_page->ID);
                } else {
                    $clinic_logo_url = get_the_post_thumbnail_url($clinic_page, 'medium');
                }

                $card = array(
                    'name' => $clinic_name,
                    'url'  => $clinic_url,
                    'logo' => $clinic_logo_url,
                );
            }
        } elseif ($custom_name || $custom_logo || $custom_url) {
            $card = array(
                'name' => $custom_name ?: 'Clinic',
                'url'  => $custom_url ?: '#',
                'logo' => $custom_logo,
            );
        } elseif (!empty($random_pool)) {
            $random_key    = array_rand($random_pool);
            $random_clinic = $random_pool[$random_key];

            $clinic_name = $custom_name ?: get_the_title($random_clinic);
            $clinic_url  = $custom_url ?: get_permalink($random_clinic);

            if ($custom_logo) {
                $clinic_logo_url = $custom_logo;
            } elseif (function_exists('cpt360_get_clinic_logo_url')) {
                $clinic_logo_url = cpt360_get_clinic_logo_url($random_clinic->ID);
            } else {
                $clinic_logo_url = get_the_post_thumbnail_url($random_clinic, 'medium');
            }

            $card = array(
                'name' => $clinic_name,
                'url'  => $clinic_url,
                'logo' => $clinic_logo_url,
            );

            array_splice($random_pool, $random_key, 1);
            $random_pool = array_values($random_pool);
        } elseif (empty($clinic_pages)) {
            $card = array(
                'name' => $custom_name ?: 'Sample Clinic ' . ($index + 1),
                'url'  => $custom_url ?: '#',
                'logo' => $custom_logo ?: '',
            );
        }

        if ($card) {
            $cards[] = $card;
        }
    }

    if (empty($cards)) {
        return '';
    }

    $card_count   = count($cards);
    $grid_classes = 'practices-grid';
    if ($card_count < 4) {
        $grid_classes .= ' practices-grid--count-' . $card_count;
    }

    $grid_style = '';
    if ($card_count > 0 && $card_count < 4) {
        $grid_width = ($card_count * 280) + max(0, ($card_count - 1) * 20);
        $grid_style = sprintf(' style="max-width:%spx"', esc_attr((string) $grid_width));
    }

    $output = '<div class="wp-block-global360blocks-popular-practices popular-practices-block">';
    $output .= '<div class="popular-practices-content">';
    $output .= '<h2 class="popular-practices-title">' . $title . '</h2>';
    $output .= '<div class="' . esc_attr($grid_classes) . '"' . $grid_style . '>';

    foreach ($cards as $card) {
        $clinic_name     = !empty($card['name']) ? $card['name'] : '';
        $clinic_url      = !empty($card['url']) ? $card['url'] : '#';
        $clinic_logo_url = !empty($card['logo']) ? $card['logo'] : '';

        $output .= '<a href="' . esc_url($clinic_url) . '" class="practice-card">';
        $output .= '<div class="practice-logo">';

        if (!empty($clinic_logo_url)) {
            $output .= '<img src="' . esc_url($clinic_logo_url) . '" alt="' . esc_attr($clinic_name) . ' Logo" />';
        } else {
            $output .= '<div class="logo-placeholder">Logo</div>';
        }

        $output .= '</div>';
        $output .= '<h3 class="practice-name">' . esc_html($clinic_name) . '</h3>';
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
        $output .= '<button class="slider-nav prev" onclick="previousSlide(this)" aria-label="Previous slide"><span class="screen-reader-text">Previous slide</span></button>';
    }
    
    $output .= '<div class="slide-container" data-current-slide="0" data-autoplay="' . ($autoplay ? 'true' : 'false') . '">';
    $output .= '<div class="slide-track">';

    foreach ($slides as $index => $slide) {
    $heading       = !empty($slide['heading']) ? wp_kses_post($slide['heading']) : '';
    $text          = !empty($slide['text']) ? wp_kses_post($slide['text']) : '';
    $image_url     = !empty($slide['imageUrl']) ? esc_url($slide['imageUrl']) : '';
    $background    = !empty($slide['contentBackground']) ? sanitize_text_field($slide['contentBackground']) : '';
        $heading_attr  = !empty($slide['heading']) ? esc_attr( wp_strip_all_tags( $slide['heading'] ) ) : '';
		
        $active_class      = $index === 0 ? 'active' : '';
        $image_state_class = $image_url ? 'has-image' : 'no-image';
    $content_style     = $background ? ' style="background-color: ' . esc_attr( $background ) . ';"' : '';

    $output .= '<div class="slide ' . $active_class . ' ' . $image_state_class . '" data-slide="' . $index . '">';
    $output .= '<div class="slide-content"' . $content_style . '>';
        $output .= '<span class="slide-index">' . ($index + 1) . '</span>';
        if ($heading) {
            $output .= '<h2 class="slide-heading">' . $heading . '</h2>';
        }
        if ($text) {
            $output .= '<p class="slide-text">' . $text . '</p>';
        }
        $output .= '</div>';
        
        if ($image_url) {
            $output .= '<div class="slide-image">';
            $output .= '<img src="' . $image_url . '" alt="' . $heading_attr . '" />';
            $output .= '</div>';
        }
        $output .= '</div>';
    }

    $output .= '</div>';
    $output .= '</div>';
    
    if ($show_arrows) {
        $output .= '<button class="slider-nav next" onclick="nextSlide(this)" aria-label="Next slide"><span class="screen-reader-text">Next slide</span></button>';
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
        function updateTwoColumnSlider(container, targetIndex) {
            if (!container) {
                return;
            }
            const track = container.querySelector(".slide-track");
            if (!track) {
                return;
            }
            const slides = Array.from(track.children);
            const dots = container.querySelectorAll(".dot");
            const total = slides.length;
            if (!total) {
                return;
            }
            let newIndex = typeof targetIndex === "number" ? targetIndex : parseInt(container.dataset.currentSlide || "0", 10);
            if (isNaN(newIndex)) {
                newIndex = 0;
            }
            newIndex = (newIndex % total + total) % total;
            container.dataset.currentSlide = newIndex;
            track.style.transform = "translateX(-" + (newIndex * 100) + "%)";
            slides.forEach(function(slide, idx) {
                slide.classList.toggle("active", idx === newIndex);
            });
            dots.forEach(function(dot, idx) {
                dot.classList.toggle("active", idx === newIndex);
            });
        }

        function nextSlide(button) {
            const container = button.closest(".two-column-slider-container");
            if (!container) {
                return;
            }
            const current = parseInt(container.dataset.currentSlide || "0", 10) || 0;
            updateTwoColumnSlider(container, current + 1);
        }

        function previousSlide(button) {
            const container = button.closest(".two-column-slider-container");
            if (!container) {
                return;
            }
            const current = parseInt(container.dataset.currentSlide || "0", 10) || 0;
            updateTwoColumnSlider(container, current - 1);
        }

        function goToSlide(button, index) {
            const container = button.closest(".two-column-slider-container");
            if (!container) {
                return;
            }
            updateTwoColumnSlider(container, index);
        }

        document.addEventListener("DOMContentLoaded", function() {
            const containers = document.querySelectorAll(".two-column-slider-container");
            containers.forEach(function(container) {
                updateTwoColumnSlider(container, parseInt(container.dataset.currentSlide || "0", 10) || 0);
            ' . ($autoplay ? '
                if (container.dataset.autoplayInitialized === "true") {
                    return;
                }
                container.dataset.autoplayInitialized = "true";
                if (container.dataset.autoplay === "true" && container.querySelectorAll(".slide").length > 1) {
                    setInterval(function() {
                        const nextButton = container.querySelector(".slider-nav.next");
                        if (nextButton) {
                            nextSlide(nextButton);
                        } else {
                            const current = parseInt(container.dataset.currentSlide || "0", 10) || 0;
                            updateTwoColumnSlider(container, current + 1);
                        }
                    }, ' . $autoplay_speed . ');
                }
            ' : '') . '
            });
        });
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
    $legacy_body_text = !empty($attributes['bodyText']) ? wp_kses_post($attributes['bodyText']) : '';
    $video_title = !empty($attributes['videoTitle']) ? wp_kses_post($attributes['videoTitle']) : '';
    
    $output = '<div class="video-two-column-block">';
    $output .= '<div class="video-two-column-container">';
    
    // Left column - Video
    $output .= '<div class="video-two-column-video">';
    if ($video_title) {
        $output .= '<h2 class="video-two-column-video-title">' . $video_title . '</h2>';
    }
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

    $body_html = '';
    if (!empty($content)) {
        $body_html = $content;
    } elseif (!empty($legacy_body_text)) {
        $body_html = $legacy_body_text;
    }

    if ($body_html) {
        $output .= '<div class="video-two-column-body">' . $body_html . '</div>';
    }
    
    // Assessment button
    if (!empty($assess_id)) {
    $output .= '<div class="video-two-column-button">';
    $output .= '<pr360-questionnaire url="wss://app.patientreach360.com/socket" site-id="' . esc_attr($assess_id) . '">Take Risk Assessment Now</pr360-questionnaire>';
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
    $output .= '<a href="/find-a-doctor/" class="btn btn_global">Find a Doctor Now</a>';
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

    register_block_type( __DIR__ . '/blocks/two-column', array(
        'render_callback' => 'global360blocks_render_two_column_block',
    ) );

    register_block_type( __DIR__ . '/blocks/two-column-text' );
    
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
    $output .= '<pr360-questionnaire url="wss://app.patientreach360.com/socket" site-id="' . esc_attr($assess_id) . '">Take Risk Assessment Now</pr360-questionnaire>';
    $output .= '</div>';
    $output .= '</div></div></div>';
    return $output;
}

if ( ! function_exists( 'global360blocks_filter_two_column_body' ) ) {
    function global360blocks_filter_two_column_body( $html, $heading = '' ) {
        if ( empty( $html ) ) {
            return $html;
        }

        $html = preg_replace( '/<img[^>]*>/i', '', $html );
        $html = preg_replace( '/Replace\s*Image\s*Remove\s*Image/i', '', $html );
        $html = preg_replace( '/Replace\s*Image/i', '', $html );
        $html = preg_replace( '/Remove\s*Image/i', '', $html );
        $html = preg_replace( '/<p[^>]*>\s*(Take\s+Risk\s+Assessment\s+Now)\s*<\/p>/i', '', $html );
        $html = preg_replace( '/<p[^>]*>\s*(Body\s+content)\s*<\/p>/i', '', $html );
        $html = preg_replace( '/<p[^>]*>\s*(?:&nbsp;|\xc2\xa0|\s)*<\/p>/i', '', $html );

        if ( ! empty( $heading ) ) {
            $quoted_heading = preg_quote( wp_strip_all_tags( $heading ), '/' );
            $heading_pattern = '/<h[1-6][^>]*>\s*' . $quoted_heading . '\s*<\/h[1-6]>/i';
            $html = preg_replace( $heading_pattern, '', $html );
        }

        return $html;
    }
}

// Render callback for Two Column block
function global360blocks_render_two_column_block( $attributes, $content, $block = null ) {
    // Get Assessment ID from theme settings (360_global_settings array)
    $global_settings = get_option('360_global_settings', []);
    $assess_id = isset($global_settings['assessment_id']) ? $global_settings['assessment_id'] : '';
    
    $image_url = !empty($attributes['imageUrl']) ? esc_url($attributes['imageUrl']) : '';
    $heading = !empty($attributes['heading']) ? wp_kses_post($attributes['heading']) : '';
    $legacy_body_text = !empty($attributes['bodyText']) ? wp_kses_post($attributes['bodyText']) : '';
    
    // Use block wrapper attributes so declared supports (e.g., align) are applied
    $wrapper_attributes = function_exists('get_block_wrapper_attributes')
        ? get_block_wrapper_attributes( array( 'class' => 'two-column-block' ) )
        : 'class="two-column-block"';
    $output = '<div ' . $wrapper_attributes . '>';
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
    $body_html = '';
    if (is_string($content) && trim($content) !== '') {
        $body_html = trim($content);
    } elseif (is_object($block) && property_exists($block, 'inner_blocks') && !empty($block->inner_blocks)) {
        foreach ($block->inner_blocks as $inner_block) {
            if (is_object($inner_block) && method_exists($inner_block, 'render')) {
                $body_html .= $inner_block->render();
            }
        }
    } elseif (!empty($legacy_body_text)) {
        $body_html = wpautop($legacy_body_text);
    }

    if ($body_html) {
        $body_html = global360blocks_filter_two_column_body( $body_html, $heading );
        $output .= '<div class="two-column-body">' . $body_html . '</div>';
    }
    $output .= '<div class="two-column-button">';
    $output .= '<pr360-questionnaire url="wss://app.patientreach360.com/socket" site-id="' . esc_attr($assess_id) . '">Take Risk Assessment Now</pr360-questionnaire>';
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

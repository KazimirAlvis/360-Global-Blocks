<?php
/**
 * 360 Global Blocks updater that consumes a JSON manifest hosted on GitHub.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SB_Global_Blocks_Updater {
    /**
     * Remote manifest URL.
     *
     * @var string
     */
    protected $manifest_url;

    /**
     * Plugin main file.
     *
     * @var string
     */
    protected $plugin_file;

    /**
     * Plugin slug.
     *
     * @var string
     */
    protected $plugin_slug;

    /**
     * Currently installed version.
     *
     * @var string
     */
    protected $version;

    /**
     * Transient cache key.
     *
     * @var string
     */
    protected $cache_key;

    /**
     * Whether remote requests may be cached.
     *
     * @var bool
     */
    protected $cache_allowed = false;

    /**
     * Last remote payload.
     *
     * @var object|null
     */
    protected $last_response = null;

    /**
     * Last error string.
     *
     * @var string
     */
    protected $last_error = '';

    /**
     * SB_Global_Blocks_Updater constructor.
     *
     * @param array $args {@see sb_global_blocks_bootstrap_updater()}.
     */
    public function __construct( $args ) {
        $defaults = array(
            'manifest_url' => '',
            'plugin_file'  => '',
            'version'      => '0.0.0',
        );

        $args           = wp_parse_args( $args, $defaults );
        $this->manifest_url = $args['manifest_url'];
        $this->plugin_file  = $args['plugin_file'];
        $this->version      = $args['version'];

        $basename          = plugin_basename( $this->plugin_file );
        $slug              = dirname( $basename );
        $this->plugin_slug = '.' === $slug ? basename( dirname( $this->plugin_file ) ) : $slug;
        $this->plugin_slug = sanitize_key( $this->plugin_slug );
        $this->cache_key   = 'sb_global_blocks_updater_' . md5( $this->plugin_slug );

        add_filter( 'plugins_api', array( $this, 'info' ), 20, 3 );
        add_filter( 'site_transient_update_plugins', array( $this, 'update' ) );
        add_action( 'upgrader_process_complete', array( $this, 'purge' ), 10, 2 );
    }

    /**
     * Retrieve remote manifest, optionally bypassing cache.
     *
     * @param bool $use_cache Whether to use the cached response.
     *
     * @return object|null
     */
    public function request( $use_cache = true ) {
        $this->last_error = '';

        if ( $this->last_response && $use_cache ) {
            return $this->last_response;
        }

        if ( $use_cache && $this->cache_allowed ) {
            $remote = get_transient( $this->cache_key );
            if ( false !== $remote ) {
                $body = wp_remote_retrieve_body( $remote );
                if ( $body ) {
                    $decoded = json_decode( $body );
                    if ( $decoded ) {
                        $this->last_response = $decoded;
                        return $this->last_response;
                    }
                }
            }
        }

        $remote = wp_remote_get(
            $this->manifest_url,
            array(
                'timeout' => 10,
                'headers' => array(
                    'Accept' => 'application/json',
                ),
            )
        );

        if ( is_wp_error( $remote ) ) {
            $this->last_error = $remote->get_error_message();
            return null;
        }

        $code = wp_remote_retrieve_response_code( $remote );
        if ( 200 !== $code ) {
            $this->last_error = sprintf( 'Unexpected HTTP %d', $code );
            return null;
        }

        $body = wp_remote_retrieve_body( $remote );
        if ( empty( $body ) ) {
            $this->last_error = 'Manifest response was empty.';
            return null;
        }

        $decoded = json_decode( $body );
        if ( ! $decoded ) {
            $this->last_error = 'Manifest response could not be decoded.';
            return null;
        }

        $this->last_response = $decoded;

        if ( $this->cache_allowed ) {
            set_transient( $this->cache_key, $remote, DAY_IN_SECONDS );
        }

        return $this->last_response;
    }

    /**
     * Enrich the plugin information modal.
     */
    public function info( $response, $action, $args ) {
        if ( 'plugin_information' !== $action ) {
            return $response;
        }

        if ( empty( $args->slug ) || $this->plugin_slug !== sanitize_key( $args->slug ) ) {
            return $response;
        }

        $remote = $this->request();
        if ( ! $remote ) {
            return $response;
        }

        $response = new stdClass();
        $response->name           = isset( $remote->name ) ? $remote->name : '360 Global Blocks';
        $response->slug           = $this->plugin_slug;
        $response->version        = isset( $remote->version ) ? $remote->version : $this->version;
        $response->tested         = isset( $remote->tested ) ? $remote->tested : get_bloginfo( 'version' );
        $response->requires       = isset( $remote->requires ) ? $remote->requires : '6.0';
        $response->requires_php   = isset( $remote->requires_php ) ? $remote->requires_php : '7.4';
        $response->author         = isset( $remote->author ) ? $remote->author : 'Kaz Alvis';
        $response->author_profile = isset( $remote->author_profile ) ? $remote->author_profile : 'https://github.com/KazimirAlvis';
        $response->homepage       = isset( $remote->homepage ) ? $remote->homepage : 'https://github.com/KazimirAlvis/360-Global-Blocks';
        $response->donate_link    = isset( $remote->donate_link ) ? $remote->donate_link : '';
        $response->last_updated   = isset( $remote->last_updated ) ? $remote->last_updated : current_time( 'mysql' );
        $response->download_link  = isset( $remote->download_url ) ? $remote->download_url : '';
        $response->sections       = array(
            'description'  => isset( $remote->sections->description ) ? $remote->sections->description : '',
            'installation' => isset( $remote->sections->installation ) ? $remote->sections->installation : '',
            'changelog'    => isset( $remote->sections->changelog ) ? $remote->sections->changelog : '',
        );

        if ( ! empty( $remote->banners ) ) {
            $response->banners = array();
            if ( isset( $remote->banners->low ) ) {
                $response->banners['low'] = $remote->banners->low;
            }
            if ( isset( $remote->banners->high ) ) {
                $response->banners['high'] = $remote->banners->high;
            }
        }

        return $response;
    }

    /**
     * Inject update details into the plugin update transient.
     */
    public function update( $transient ) {
        if ( empty( $transient->checked ) ) {
            return $transient;
        }

        $remote = $this->request();
        if ( ! $remote || empty( $remote->version ) ) {
            return $transient;
        }

        $remote_version = $remote->version;
        $requires_wp    = isset( $remote->requires ) ? $remote->requires : '0';
        $requires_php   = isset( $remote->requires_php ) ? $remote->requires_php : '0';
        $download_url   = isset( $remote->download_url ) ? $remote->download_url : '';

        if (
            version_compare( $this->version, $remote_version, '<' ) &&
            version_compare( $requires_wp, get_bloginfo( 'version' ), '<=' ) &&
            version_compare( $requires_php, PHP_VERSION, '<=' ) &&
            $download_url
        ) {
            $response            = new stdClass();
            $response->slug      = $this->plugin_slug;
            $response->plugin    = plugin_basename( $this->plugin_file );
            $response->new_version = $remote_version;
            $response->tested      = isset( $remote->tested ) ? $remote->tested : get_bloginfo( 'version' );
            $response->package     = $download_url;

            $transient->response[ $response->plugin ] = $response;
        }

        return $transient;
    }

    /**
     * Clear cached manifest when an update completes.
     */
    public function purge( $upgrader, $options ) {
        if ( isset( $options['action'], $options['type'] ) && 'update' === $options['action'] && 'plugin' === $options['type'] ) {
            $this->clear_cache();
        }
    }

    /**
     * Delete cached manifest.
     */
    public function clear_cache() {
        delete_transient( $this->cache_key );
        $this->last_response = null;
    }

    /**
     * Force WordPress to check updates immediately.
     */
    public function force_check() {
        $this->clear_cache();
        delete_site_transient( 'update_plugins' );
        if ( function_exists( 'wp_update_plugins' ) ) {
            wp_update_plugins();
        }
    }

    /**
     * Get the plugin version.
     */
    public function get_version() {
        return $this->version;
    }

    /**
     * Get the manifest URL.
     */
    public function get_manifest_url() {
        return $this->manifest_url;
    }

    /**
     * Get the plugin file.
     */
    public function get_plugin_file() {
        return $this->plugin_file;
    }

    /**
     * Get the most recent error string, if any.
     */
    public function get_last_error() {
        return $this->last_error;
    }
}

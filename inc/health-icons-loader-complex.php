<?php

/**
 * Health Icons Loader
 * Manages the official Health Icons library for the 360 Global Blocks plugin
 * Uses: https://github.com/resolvetosavelives/healthicons
 */

if (!defined('ABSPATH')) {
    exit;
}

class HealthIconsLoader {
    private static $instance = null;
    private $icons_cache = null;
    private $metadata_cache = null;
    private $health_icons_path;

    public function __construct() {
        $this->health_icons_path = plugin_dir_path(__FILE__) . '../node_modules/healthicons/public/icons/';
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Load metadata from the official Health Icons library
     */
    private function loadMetadata() {
        if ($this->metadata_cache !== null) {
            return $this->metadata_cache;
        }

        $metadata_file = $this->health_icons_path . 'meta-data.json';
        
        if (!file_exists($metadata_file)) {
            error_log('Health Icons metadata not found: ' . $metadata_file);
            // Return fallback data if metadata file is not available
            $this->metadata_cache = array();
            return array();
        }

        $metadata_content = @file_get_contents($metadata_file);
        if ($metadata_content === false) {
            error_log('Failed to read Health Icons metadata file');
            $this->metadata_cache = [];
            return [];
        }

        $metadata = json_decode($metadata_content, true);
        
        if (!$metadata || !is_array($metadata)) {
            error_log('Failed to parse Health Icons metadata or invalid format');
            $this->metadata_cache = [];
            return [];
        }

        $this->metadata_cache = $metadata;
        return $metadata;
    }

    /**
     * Get all available Health Icons organized by category
     * Filters to include only commonly used medical icons
     */
    public function getAllIcons() {
        if ($this->icons_cache !== null) {
            return $this->icons_cache;
        }

        $metadata = $this->loadMetadata();
        $icons = array();
        
        // If metadata is available, use it
        if (!empty($metadata)) {
            // Define medical categories we want to include
            $medical_categories = array(
                'body', 'devices', 'people', 'specialties', 
                'conditions', 'medications', 'diagnostics'
            );

            // Filter for relevant medical icons
            foreach ($metadata as $icon_data) {
                $category = $icon_data['category'] ?? '';
                
                // Only include medical categories
                if (!in_array($category, $medical_categories)) {
                    continue;
                }

                // Check if SVG file exists
                $svg_path = $this->health_icons_path . 'svg/filled/' . $icon_data['path'] . '.svg';
                if (!file_exists($svg_path)) {
                    continue;
                }

                $icon_key = $icon_data['path'];
                $icons[$icon_key] = [
                    'name' => $icon_data['title'] ?? $this->formatIconName($icon_data['id']),
                    'category' => $this->formatCategoryName($category),
                    'id' => $icon_data['id'],
                    'tags' => $icon_data['tags'] ?? []
                ];
            }
        }

        // Add or ensure specific popular medical icons exist
        $popular_icons = [
            'body/heart_organ' => 'Heart',
            'body/lungs' => 'Lungs', 
            'body/neurology' => 'Brain',
            'devices/stethoscope' => 'Stethoscope',
            'devices/syringe' => 'Syringe',
            'devices/thermometer_digital' => 'Digital Thermometer',
            'devices/blood_pressure' => 'Blood Pressure Monitor',
            'people/doctor' => 'Doctor',
            'people/nurse' => 'Nurse'
        ];

        foreach ($popular_icons as $path => $title) {
            $svg_path = $this->health_icons_path . 'svg/filled/' . $path . '.svg';
            if (file_exists($svg_path)) {
                // Only add if not already in icons array
                if (!isset($icons[$path])) {
                    $category = explode('/', $path)[0];
                    $icons[$path] = [
                        'name' => $title,
                        'category' => $this->formatCategoryName($category),
                        'id' => basename($path),
                        'tags' => []
                    ];
                }
            } else {
                // If Health Icons library files don't exist, provide fallback
                $category = explode('/', $path)[0];
                $icons[$path] = [
                    'name' => $title,
                    'category' => $this->formatCategoryName($category),
                    'id' => basename($path),
                    'tags' => []
                ];
            }
        }

        // If we still have no icons, provide basic fallbacks
        if (empty($icons)) {
            $icons = [
                'devices/stethoscope' => [
                    'name' => 'Stethoscope',
                    'category' => 'Medical Devices',
                    'id' => 'stethoscope',
                    'tags' => []
                ],
                'body/heart_organ' => [
                    'name' => 'Heart',
                    'category' => 'Body & Anatomy',
                    'id' => 'heart_organ',
                    'tags' => []
                ],
                'people/doctor' => [
                    'name' => 'Doctor',
                    'category' => 'Healthcare People',
                    'id' => 'doctor',
                    'tags' => []
                ]
            ];
        }

        $this->icons_cache = $icons;
        return $icons;
    }

    /**
     * Get a specific Health Icon SVG content from the official library
     */
    public function getIcon($icon_key) {
        if (empty($icon_key)) {
            return null;
        }

        // Try filled style first
        $icon_path = $this->health_icons_path . 'svg/filled/' . $icon_key . '.svg';
        
        if (!file_exists($icon_path)) {
            // Try outline style as fallback
            $icon_path = $this->health_icons_path . 'svg/outline/' . $icon_key . '.svg';
        }
        
        if (!file_exists($icon_path)) {
            error_log('Health Icon not found: ' . $icon_key);
            // Return a simple fallback SVG
            return $this->getFallbackSvg($icon_key);
        }

        $svg_content = @file_get_contents($icon_path);
        if ($svg_content === false) {
            error_log('Failed to read Health Icon file: ' . $icon_path);
            return $this->getFallbackSvg($icon_key);
        }

        return $svg_content;
    }

    /**
     * Get a simple fallback SVG for when Health Icons are not available
     */
    private function getFallbackSvg($icon_key) {
        // Simple medical cross icon as fallback
        return '<svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="18" y="12" width="12" height="24" fill="currentColor"/>
            <rect x="12" y="18" width="24" height="12" fill="currentColor"/>
        </svg>';
    }

    /**
     * Render an icon with proper attributes
     */
    public function renderIcon($icon_key, $attributes = array()) {
        $svg_content = $this->getIcon($icon_key);
        
        if (!$svg_content) {
            return '';
        }

        // Add default attributes
        $default_attributes = array(
            'class' => 'health-icon',
            'role' => 'img',
            'aria-hidden' => 'true'
        );

        $attributes = array_merge($default_attributes, $attributes);

        // Inject attributes into SVG
        $svg_content = $this->injectSvgAttributes($svg_content, $attributes);

        return $svg_content;
    }

    /**
     * Inject attributes into SVG element
     */
    private function injectSvgAttributes($svg_content, $attributes) {
        $attributes_string = '';
        
        foreach ($attributes as $key => $value) {
            $attributes_string .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
        }

        // Replace the opening SVG tag with attributes
        $svg_content = preg_replace(
            '/<svg([^>]*)>/',
            '<svg$1' . $attributes_string . '>',
            $svg_content,
            1
        );

        return $svg_content;
    }

    /**
     * Format icon name for display
     */
    private function formatIconName($name) {
        // Convert underscores to spaces and title case
        $formatted = str_replace(['_', '-'], ' ', $name);
        return ucwords($formatted);
    }

    /**
     * Format category name for display
     */
    private function formatCategoryName($category) {
        $category_map = [
            'body' => 'Body & Anatomy',
            'devices' => 'Medical Devices', 
            'people' => 'Healthcare People',
            'specialties' => 'Medical Specialties',
            'conditions' => 'Health Conditions',
            'medications' => 'Medications',
            'diagnostics' => 'Diagnostics'
        ];

        return isset($category_map[$category]) ? $category_map[$category] : ucwords($category);
    }

    /**
     * Get icon categories
     */
    public function getCategories() {
        $icons = $this->getAllIcons();
        $categories = [];
        
        foreach ($icons as $icon_data) {
            $category = $icon_data['category'];
            if (!in_array($category, $categories)) {
                $categories[] = $category;
            }
        }
        
        return $categories;
    }

    /**
     * Search icons by name, category, or tags
     */
    public function searchIcons($search_term) {
        if (empty($search_term)) {
            return $this->getAllIcons();
        }

        $icons = $this->getAllIcons();
        $filtered = [];
        
        $search_lower = strtolower($search_term);
        
        foreach ($icons as $key => $icon_data) {
            $name_match = strpos(strtolower($icon_data['name']), $search_lower) !== false;
            $category_match = strpos(strtolower($icon_data['category']), $search_lower) !== false;
            
            // Also search in tags
            $tag_match = false;
            foreach ($icon_data['tags'] as $tag) {
                if (strpos(strtolower($tag), $search_lower) !== false) {
                    $tag_match = true;
                    break;
                }
            }
            
            if ($name_match || $category_match || $tag_match) {
                $filtered[$key] = $icon_data;
            }
        }
        
        return $filtered;
    }

    /**
     * Check if an icon exists
     */
    public function iconExists($icon_key) {
        if (empty($icon_key)) {
            return false;
        }

        $filled_path = $this->health_icons_path . 'svg/filled/' . $icon_key . '.svg';
        $outline_path = $this->health_icons_path . 'svg/outline/' . $icon_key . '.svg';
        
        return file_exists($filled_path) || file_exists($outline_path);
    }

    /**
     * Get default fallback icons that are guaranteed to exist
     */
    public function getDefaultIcons() {
        $defaults = [
            'devices/stethoscope',
            'body/heart_organ', 
            'people/doctor'
        ];
        
        // Filter to only return icons that actually exist
        $existing_defaults = [];
        foreach ($defaults as $icon_key) {
            if ($this->iconExists($icon_key)) {
                $existing_defaults[] = $icon_key;
            }
        }
        
        return $existing_defaults;
    }

    /**
     * Get icons by category
     */
    public function getIconsByCategory($category) {
        $icons = $this->getAllIcons();
        $filtered = [];
        
        foreach ($icons as $key => $icon_data) {
            if (strtolower($icon_data['category']) === strtolower($category)) {
                $filtered[$key] = $icon_data;
            }
        }
        
        return $filtered;
    }
}

/**
 * Convenience functions
 */
function get_health_icon($icon_key, $attributes = array()) {
    $loader = HealthIconsLoader::getInstance();
    return $loader->renderIcon($icon_key, $attributes);
}

function get_all_health_icons() {
    $loader = HealthIconsLoader::getInstance();
    return $loader->getAllIcons();
}

function health_icon_exists($icon_key) {
    $loader = HealthIconsLoader::getInstance();
    return $loader->iconExists($icon_key);
}
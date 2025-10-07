<?php

/**
 * Health Icons Loader - Simplified Version
 * Manages the official Health Icons library for the 360 Global Blocks plugin
 * Uses: https://github.com/resolvetosavelives/healthicons
 */

if (!defined('ABSPATH')) {
    exit;
}

class HealthIconsLoader {
    private static $instance = null;
    private $icons_cache = null;
    private $health_icons_path;

    public function __construct() {
        $this->health_icons_path = plugin_dir_path(__FILE__) . '../assets/healthicons/';
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get all available Health Icons - returns curated list
     */
    public function getAllIcons() {
        if ($this->icons_cache !== null) {
            return $this->icons_cache;
        }

        // Return curated list of popular medical icons
        $this->icons_cache = array(
            'body/heart_organ' => array(
                'name' => 'Heart',
                'category' => 'Body & Anatomy',
                'id' => 'heart_organ',
                'tags' => array()
            ),
            'body/lungs' => array(
                'name' => 'Lungs',
                'category' => 'Body & Anatomy',
                'id' => 'lungs',
                'tags' => array()
            ),
            'body/neurology' => array(
                'name' => 'Brain',
                'category' => 'Body & Anatomy',
                'id' => 'neurology',
                'tags' => array()
            ),
            'devices/stethoscope' => array(
                'name' => 'Stethoscope',
                'category' => 'Medical Devices',
                'id' => 'stethoscope',
                'tags' => array()
            ),
            'devices/syringe' => array(
                'name' => 'Syringe',
                'category' => 'Medical Devices',
                'id' => 'syringe',
                'tags' => array()
            ),
            'devices/thermometer_digital' => array(
                'name' => 'Digital Thermometer',
                'category' => 'Medical Devices',
                'id' => 'thermometer_digital',
                'tags' => array()
            ),
            'devices/blood_pressure' => array(
                'name' => 'Blood Pressure Monitor',
                'category' => 'Medical Devices',
                'id' => 'blood_pressure',
                'tags' => array()
            ),
            'people/doctor' => array(
                'name' => 'Doctor',
                'category' => 'Healthcare People',
                'id' => 'doctor',
                'tags' => array()
            ),
            'people/nurse' => array(
                'name' => 'Nurse',
                'category' => 'Healthcare People',
                'id' => 'nurse',
                'tags' => array()
            ),
            'conditions/tb' => array(
                'name' => 'Tuberculosis',
                'category' => 'Health Conditions',
                'id' => 'tb',
                'tags' => array()
            ),
            'conditions/pneumonia' => array(
                'name' => 'Pneumonia',
                'category' => 'Health Conditions',
                'id' => 'pneumonia',
                'tags' => array()
            ),
            'medications/medicines' => array(
                'name' => 'Medicines',
                'category' => 'Medications',
                'id' => 'medicines',
                'tags' => array()
            )
        );

        return $this->icons_cache;
    }

    /**
     * Get a specific Health Icon SVG content from the extracted icons
     */
    public function getIcon($icon_key) {
        if (empty($icon_key)) {
            return null;
        }

        // Extract just the icon name from the full path (e.g., "body/heart_organ" -> "heart_organ")
        $icon_name = basename($icon_key);
        
        // All extracted icons are in the assets/healthicons/ directory
        $icon_path = $this->health_icons_path . $icon_name . '.svg';
        
        if (!file_exists($icon_path)) {
            // Return a simple fallback SVG
            return $this->getFallbackSvg($icon_key);
        }

        $svg_content = @file_get_contents($icon_path);
        if ($svg_content === false) {
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
            if (function_exists('esc_attr')) {
                $attributes_string .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
            } else {
                // Fallback for when WordPress functions aren't available
                $attributes_string .= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
            }
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
     * Check if an icon exists
     */
    public function iconExists($icon_key) {
        if (empty($icon_key)) {
            return false;
        }

        // Extract just the icon name from the full path (e.g., "body/heart_organ" -> "heart_organ")
        $icon_name = basename($icon_key);
        $icon_path = $this->health_icons_path . $icon_name . '.svg';
        
        return file_exists($icon_path);
    }

    /**
     * Get icon categories
     */
    public function getCategories() {
        $icons = $this->getAllIcons();
        $categories = array();
        
        foreach ($icons as $icon_data) {
            $category = $icon_data['category'];
            if (!in_array($category, $categories)) {
                $categories[] = $category;
            }
        }
        
        return $categories;
    }

    /**
     * Get default fallback icons that are guaranteed to exist
     */
    public function getDefaultIcons() {
        return array(
            'devices/stethoscope',
            'body/heart_organ', 
            'people/doctor'
        );
    }

    /**
     * Get icons by category
     */
    public function getIconsByCategory($category) {
        $icons = $this->getAllIcons();
        $filtered = array();
        
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
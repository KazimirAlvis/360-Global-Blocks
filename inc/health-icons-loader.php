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
     * Get all available Health Icons - returns comprehensive list
     */
    public function getAllIcons() {
        if ($this->icons_cache !== null) {
            return $this->icons_cache;
        }

        // Comprehensive Health Icons collection organized by categories
        $this->icons_cache = array(
            // Body Parts & Anatomy
            'body/heart_organ' => array(
                'name' => 'Heart',
                'category' => 'Body & Anatomy',
                'id' => 'heart_organ',
                'tags' => array('organ', 'cardiovascular', 'cardiology')
            ),
            'body/lungs' => array(
                'name' => 'Lungs',
                'category' => 'Body & Anatomy',
                'id' => 'lungs',
                'tags' => array('organ', 'respiratory', 'breathing')
            ),
            'body/brain' => array(
                'name' => 'Brain',
                'category' => 'Body & Anatomy',
                'id' => 'brain',
                'tags' => array('organ', 'neurology', 'mental')
            ),
            'body/eye' => array(
                'name' => 'Eye',
                'category' => 'Body & Anatomy',
                'id' => 'eye',
                'tags' => array('vision', 'sight', 'ophthalmology')
            ),
            'body/ear' => array(
                'name' => 'Ear',
                'category' => 'Body & Anatomy',
                'id' => 'ear',
                'tags' => array('hearing', 'audiology', 'ENT')
            ),
            'body/tooth' => array(
                'name' => 'Tooth',
                'category' => 'Body & Anatomy',
                'id' => 'tooth',
                'tags' => array('dental', 'oral', 'dentistry')
            ),
            'body/kidneys' => array(
                'name' => 'Kidneys',
                'category' => 'Body & Anatomy',
                'id' => 'kidneys',
                'tags' => array('organ', 'nephrology', 'urology')
            ),
            'body/liver' => array(
                'name' => 'Liver',
                'category' => 'Body & Anatomy',
                'id' => 'liver',
                'tags' => array('organ', 'hepatology', 'digestive')
            ),
            'body/stomach' => array(
                'name' => 'Stomach',
                'category' => 'Body & Anatomy',
                'id' => 'stomach',
                'tags' => array('organ', 'digestive', 'gastroenterology')
            ),
            'body/spine' => array(
                'name' => 'Spine',
                'category' => 'Body & Anatomy',
                'id' => 'spine',
                'tags' => array('bone', 'orthopedics', 'back')
            ),
            
            // Medical Conditions
            'conditions/allergies' => array(
                'name' => 'Allergies',
                'category' => 'Medical Conditions',
                'id' => 'allergies',
                'tags' => array('allergy', 'reaction', 'immune')
            ),
            'conditions/headache' => array(
                'name' => 'Headache',
                'category' => 'Medical Conditions',
                'id' => 'headache',
                'tags' => array('pain', 'migraine', 'head')
            ),
            'conditions/fever' => array(
                'name' => 'Fever',
                'category' => 'Medical Conditions',
                'id' => 'fever',
                'tags' => array('temperature', 'illness', 'infection')
            ),
            'conditions/coughing' => array(
                'name' => 'Coughing',
                'category' => 'Medical Conditions',
                'id' => 'coughing',
                'tags' => array('respiratory', 'symptom', 'throat')
            ),
            'conditions/diarrhea' => array(
                'name' => 'Diarrhea',
                'category' => 'Medical Conditions',
                'id' => 'diarrhea',
                'tags' => array('digestive', 'symptom', 'gastro')
            ),
            'conditions/nausea' => array(
                'name' => 'Nausea',
                'category' => 'Medical Conditions',
                'id' => 'nausea',
                'tags' => array('stomach', 'symptom', 'digestive')
            ),
            'conditions/back_pain' => array(
                'name' => 'Back Pain',
                'category' => 'Medical Conditions',
                'id' => 'back_pain',
                'tags' => array('pain', 'spine', 'orthopedic')
            ),
            'conditions/diabetes' => array(
                'name' => 'Diabetes',
                'category' => 'Medical Conditions',
                'id' => 'diabetes',
                'tags' => array('blood sugar', 'endocrine', 'chronic')
            ),
            'conditions/overweight' => array(
                'name' => 'Overweight',
                'category' => 'Medical Conditions',
                'id' => 'overweight',
                'tags' => array('weight', 'obesity', 'nutrition')
            ),
            'conditions/underweight' => array(
                'name' => 'Underweight',
                'category' => 'Medical Conditions',
                'id' => 'underweight',
                'tags' => array('weight', 'malnutrition', 'nutrition')
            ),
            'conditions/pneumonia' => array(
                'name' => 'Pneumonia',
                'category' => 'Medical Conditions',
                'id' => 'pneumonia',
                'tags' => array('respiratory', 'infection', 'lungs')
            ),
            'conditions/tb' => array(
                'name' => 'Tuberculosis',
                'category' => 'Medical Conditions',
                'id' => 'tb',
                'tags' => array('infectious', 'respiratory', 'TB')
            ),
            
            // Medical Devices
            'devices/stethoscope' => array(
                'name' => 'Stethoscope',
                'category' => 'Medical Devices',
                'id' => 'stethoscope',
                'tags' => array('diagnostic', 'examination', 'cardiology')
            ),
            'devices/syringe' => array(
                'name' => 'Syringe',
                'category' => 'Medical Devices',
                'id' => 'syringe',
                'tags' => array('injection', 'vaccination', 'medication')
            ),
            'devices/thermometer_digital' => array(
                'name' => 'Digital Thermometer',
                'category' => 'Medical Devices',
                'id' => 'thermometer_digital',
                'tags' => array('temperature', 'fever', 'diagnostic')
            ),
            'devices/blood_pressure' => array(
                'name' => 'Blood Pressure Monitor',
                'category' => 'Medical Devices',
                'id' => 'blood_pressure',
                'tags' => array('cardiovascular', 'monitoring', 'hypertension')
            ),
            'devices/microscope' => array(
                'name' => 'Microscope',
                'category' => 'Medical Devices',
                'id' => 'microscope',
                'tags' => array('laboratory', 'diagnostic', 'research')
            ),
            'devices/wheelchair' => array(
                'name' => 'Wheelchair',
                'category' => 'Medical Devices',
                'id' => 'wheelchair',
                'tags' => array('mobility', 'disability', 'accessibility')
            ),
            'devices/xray' => array(
                'name' => 'X-Ray',
                'category' => 'Medical Devices',
                'id' => 'xray',
                'tags' => array('imaging', 'radiology', 'diagnostic')
            ),
            'devices/ultrasound' => array(
                'name' => 'Ultrasound Scanner',
                'category' => 'Medical Devices',
                'id' => 'ultrasound',
                'tags' => array('imaging', 'diagnostic', 'obstetrics')
            ),
            'devices/defibrillator' => array(
                'name' => 'Defibrillator',
                'category' => 'Medical Devices',
                'id' => 'defibrillator',
                'tags' => array('emergency', 'cardiology', 'resuscitation')
            ),
            'devices/cpap_machine' => array(
                'name' => 'CPAP Machine',
                'category' => 'Medical Devices',
                'id' => 'cpap_machine',
                'tags' => array('respiratory', 'sleep', 'breathing')
            ),
            'devices/medicines' => array(
                'name' => 'Medicines',
                'category' => 'Medical Devices',
                'id' => 'medicines',
                'tags' => array('medication', 'pharmacy', 'treatment')
            ),
            
            // Healthcare People
            'people/doctor' => array(
                'name' => 'Doctor',
                'category' => 'Healthcare People',
                'id' => 'doctor',
                'tags' => array('physician', 'medical professional', 'healthcare')
            ),
            'people/nurse' => array(
                'name' => 'Nurse',
                'category' => 'Healthcare People',
                'id' => 'nurse',
                'tags' => array('nursing', 'healthcare', 'medical professional')
            ),
            'people/doctor_female' => array(
                'name' => 'Female Doctor',
                'category' => 'Healthcare People',
                'id' => 'doctor_female',
                'tags' => array('physician', 'woman', 'medical professional')
            ),
            'people/doctor_male' => array(
                'name' => 'Male Doctor',
                'category' => 'Healthcare People',
                'id' => 'doctor_male',
                'tags' => array('physician', 'man', 'medical professional')
            ),
            'people/elderly' => array(
                'name' => 'Elderly Person',
                'category' => 'Healthcare People',
                'id' => 'elderly',
                'tags' => array('senior', 'geriatric', 'aging')
            ),
            'people/pregnant' => array(
                'name' => 'Pregnant Woman',
                'category' => 'Healthcare People',
                'id' => 'pregnant',
                'tags' => array('pregnancy', 'maternity', 'obstetrics')
            ),
            'people/baby' => array(
                'name' => 'Baby',
                'category' => 'Healthcare People',
                'id' => 'baby',
                'tags' => array('infant', 'pediatric', 'child')
            ),
            'people/community_health_worker' => array(
                'name' => 'Community Health Worker',
                'category' => 'Healthcare People',
                'id' => 'community_health_worker',
                'tags' => array('community', 'outreach', 'public health')
            ),
            'people/emergency_worker' => array(
                'name' => 'Emergency Worker',
                'category' => 'Healthcare People',
                'id' => 'emergency_worker',
                'tags' => array('emergency', 'first responder', 'paramedic')
            ),
            'people/person' => array(
                'name' => 'Person',
                'category' => 'Healthcare People',
                'id' => 'person',
                'tags' => array('patient', 'individual', 'human')
            ),
            
            // Medical Specialties
            'specialties/cardiology' => array(
                'name' => 'Cardiology',
                'category' => 'Medical Specialties',
                'id' => 'cardiology',
                'tags' => array('heart', 'cardiovascular', 'cardiac')
            ),
            'specialties/neurology' => array(
                'name' => 'Neurology',
                'category' => 'Medical Specialties',
                'id' => 'neurology',
                'tags' => array('brain', 'nervous system', 'neurological')
            ),
            'specialties/pediatrics' => array(
                'name' => 'Pediatrics',
                'category' => 'Medical Specialties',
                'id' => 'pediatrics',
                'tags' => array('children', 'child health', 'pediatric')
            ),
            'specialties/orthopedics' => array(
                'name' => 'Orthopedics',
                'category' => 'Medical Specialties',
                'id' => 'orthopedics',
                'tags' => array('bones', 'joints', 'musculoskeletal')
            ),
            'specialties/radiology' => array(
                'name' => 'Radiology',
                'category' => 'Medical Specialties',
                'id' => 'radiology',
                'tags' => array('imaging', 'x-ray', 'diagnostic')
            ),
            'specialties/pharmacy' => array(
                'name' => 'Pharmacy',
                'category' => 'Medical Specialties',
                'id' => 'pharmacy',
                'tags' => array('medication', 'drugs', 'pharmaceutical')
            ),
            'specialties/psychology' => array(
                'name' => 'Psychology',
                'category' => 'Medical Specialties',
                'id' => 'psychology',
                'tags' => array('mental health', 'behavioral', 'therapy')
            ),
            'specialties/physical_therapy' => array(
                'name' => 'Physical Therapy',
                'category' => 'Medical Specialties',
                'id' => 'physical_therapy',
                'tags' => array('rehabilitation', 'movement', 'physiotherapy')
            ),
            'specialties/emergency_department' => array(
                'name' => 'Emergency Department',
                'category' => 'Medical Specialties',
                'id' => 'emergency_department',
                'tags' => array('emergency', 'urgent care', 'trauma')
            ),
            'specialties/intensive_care' => array(
                'name' => 'Intensive Care',
                'category' => 'Medical Specialties',
                'id' => 'intensive_care',
                'tags' => array('ICU', 'critical care', 'intensive')
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
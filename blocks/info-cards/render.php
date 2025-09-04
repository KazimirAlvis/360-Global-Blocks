<?php
/**
 * Info Cards Block Render
 */

if (!defined('ABSPATH')) {
    exit;
}

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
?>
<div <?php echo $wrapper_attributes; ?>>
    <div class="info-cards-container">
        <?php if (!empty($main_title)): ?>
            <h2 class="info-cards-main-title"><?php echo esc_html($main_title); ?></h2>
        <?php endif; ?>
        
        <div class="info-cards-grid">
            <?php foreach ($cards as $card): ?>
                <?php 
                $icon_key = isset($card['icon']) ? $card['icon'] : 'stethoscope';
                $icon_data = isset($medical_icons[$icon_key]) ? $medical_icons[$icon_key] : $medical_icons['stethoscope'];
                $card_title = isset($card['title']) ? $card['title'] : '';
                $card_text = isset($card['text']) ? $card['text'] : '';
                ?>
                <div class="info-card">
                    <div class="info-card-icon-wrapper">
                        <svg 
                            width="64" 
                            height="64" 
                            viewBox="0 0 24 24" 
                            fill="var(--cpt360-primary, #0073aa)"
                            stroke="var(--cpt360-primary, #0073aa)"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="info-card-icon"
                            role="img"
                            aria-labelledby="icon-title-<?php echo esc_attr($icon_key); ?>"
                        >
                            <title id="icon-title-<?php echo esc_attr($icon_key); ?>"><?php echo esc_html($icon_data['name']); ?></title>
                            <?php echo $icon_data['path']; ?>
                        </svg>
                    </div>
                    
                    <?php if (!empty($card_title)): ?>
                        <h3 class="info-card-title"><?php echo wp_kses_post($card_title); ?></h3>
                    <?php endif; ?>
                    
                    <?php if (!empty($card_text)): ?>
                        <p class="info-card-text"><?php echo wp_kses_post($card_text); ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

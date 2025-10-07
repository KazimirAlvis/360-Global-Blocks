/**
 * Health Icons JavaScript Utilities
 * Provides client-side functionality for Health Icons
 */

// Global Health Icons utilities
window.HealthIcons = window.HealthIcons || {
    
    // Cache for loaded icons
    cache: new Map(),
    
    /**
     * Get icon data from WordPress AJAX
     */
    async loadIcon(iconKey) {
        if (this.cache.has(iconKey)) {
            return this.cache.get(iconKey);
        }
        
        if (typeof healthIconsAjax === 'undefined') {
            console.warn('Health Icons AJAX not available');
            return null;
        }
        
        try {
            const response = await fetch(healthIconsAjax.ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    action: 'get_health_icon',
                    icon_key: iconKey,
                    nonce: healthIconsAjax.nonce
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.cache.set(iconKey, data.data);
                return data.data;
            }
        } catch (error) {
            console.error('Error loading health icon:', error);
        }
        
        return null;
    },
    
    /**
     * Clean SVG for CSS styling
     */
    cleanSvgForCss(svgContent) {
        if (!svgContent) return svgContent;
        
        // Remove fill and stroke attributes except "none"
        let cleanedSvg = svgContent
            .replace(/fill="(?!none)[^"]*"/g, '')
            .replace(/stroke="(?!none)[^"]*"/g, '');
        
        // Add currentColor to the root SVG element
        cleanedSvg = cleanedSvg.replace(
            /<svg([^>]*)>/,
            '<svg$1 fill="currentColor">'
        );
        
        return cleanedSvg;
    },
    
    /**
     * Get all available icons
     */
    getAllIcons() {
        if (typeof healthIconsAjax !== 'undefined' && healthIconsAjax.all_icons) {
            return healthIconsAjax.all_icons;
        }
        
        // Fallback icons
        return {
            'body/heart': { name: 'Heart', category: 'Body Parts & Anatomy' },
            'devices/stethoscope': { name: 'Stethoscope', category: 'Medical Devices' },
            'devices/thermometer_digital': { name: 'Thermometer', category: 'Medical Devices' },
            'body/lungs': { name: 'Lungs', category: 'Body Parts & Anatomy' },
            'body/brain': { name: 'Brain', category: 'Body Parts & Anatomy' },
            'devices/syringe': { name: 'Syringe', category: 'Medical Devices' },
            'people/doctor': { name: 'Doctor', category: 'Healthcare People' },
            'specialties/cardiology': { name: 'Cardiology', category: 'Medical Specialties' }
        };
    },
    
    /**
     * Format icons for select dropdown
     */
    getIconOptions() {
        const icons = this.getAllIcons();
        return Object.entries(icons).map(([key, icon]) => ({
            label: `${icon.name} (${icon.category})`,
            value: key
        }));
    }
};
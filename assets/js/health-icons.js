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
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams({
					action: 'get_health_icon',
					icon_key: iconKey,
					nonce: healthIconsAjax.nonce,
				}),
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
		let cleanedSvg = svgContent.replace(/fill="(?!none)[^"]*"/g, '').replace(/stroke="(?!none)[^"]*"/g, '');

		// Add currentColor to the root SVG element
		cleanedSvg = cleanedSvg.replace(/<svg([^>]*)>/, '<svg$1 fill="currentColor">');

		return cleanedSvg;
	},

	/**
	 * Get all available icons
	 */
	getAllIcons() {
		if (typeof healthIconsAjax !== 'undefined' && healthIconsAjax.all_icons) {
			return healthIconsAjax.all_icons;
		}

		// Comprehensive Health Icons collection
		return {
			// Body Parts & Anatomy
			'body/heart_organ': { name: 'Heart', category: 'Body Parts & Anatomy' },
			'body/lungs': { name: 'Lungs', category: 'Body Parts & Anatomy' },
			'body/brain': { name: 'Brain', category: 'Body Parts & Anatomy' },
			'body/eye': { name: 'Eye', category: 'Body Parts & Anatomy' },
			'body/ear': { name: 'Ear', category: 'Body Parts & Anatomy' },
			'body/tooth': { name: 'Tooth', category: 'Body Parts & Anatomy' },
			'body/kidneys': { name: 'Kidneys', category: 'Body Parts & Anatomy' },
			'body/liver': { name: 'Liver', category: 'Body Parts & Anatomy' },
			'body/stomach': { name: 'Stomach', category: 'Body Parts & Anatomy' },
			'body/spine': { name: 'Spine', category: 'Body Parts & Anatomy' },

			// Medical Conditions
			'conditions/allergies': { name: 'Allergies', category: 'Medical Conditions' },
			'conditions/headache': { name: 'Headache', category: 'Medical Conditions' },
			'conditions/fever': { name: 'Fever', category: 'Medical Conditions' },
			'conditions/coughing': { name: 'Coughing', category: 'Medical Conditions' },
			'conditions/diarrhea': { name: 'Diarrhea', category: 'Medical Conditions' },
			'conditions/nausea': { name: 'Nausea', category: 'Medical Conditions' },
			'conditions/back_pain': { name: 'Back Pain', category: 'Medical Conditions' },
			'conditions/diabetes': { name: 'Diabetes', category: 'Medical Conditions' },
			'conditions/overweight': { name: 'Overweight', category: 'Medical Conditions' },
			'conditions/underweight': { name: 'Underweight', category: 'Medical Conditions' },
			'conditions/pneumonia': { name: 'Pneumonia', category: 'Medical Conditions' },
			'conditions/tb': { name: 'Tuberculosis', category: 'Medical Conditions' },

			// Medical Devices
			'devices/stethoscope': { name: 'Stethoscope', category: 'Medical Devices' },
			'devices/syringe': { name: 'Syringe', category: 'Medical Devices' },
			'devices/thermometer_digital': { name: 'Digital Thermometer', category: 'Medical Devices' },
			'devices/blood_pressure': { name: 'Blood Pressure Monitor', category: 'Medical Devices' },
			'devices/microscope': { name: 'Microscope', category: 'Medical Devices' },
			'devices/wheelchair': { name: 'Wheelchair', category: 'Medical Devices' },
			'devices/xray': { name: 'X-Ray', category: 'Medical Devices' },
			'devices/ultrasound': { name: 'Ultrasound Scanner', category: 'Medical Devices' },
			'devices/defibrillator': { name: 'Defibrillator', category: 'Medical Devices' },
			'devices/cpap_machine': { name: 'CPAP Machine', category: 'Medical Devices' },
			'devices/medicines': { name: 'Medicines', category: 'Medical Devices' },

			// Healthcare People
			'people/doctor': { name: 'Doctor', category: 'Healthcare People' },
			'people/nurse': { name: 'Nurse', category: 'Healthcare People' },
			'people/doctor_female': { name: 'Female Doctor', category: 'Healthcare People' },
			'people/doctor_male': { name: 'Male Doctor', category: 'Healthcare People' },
			'people/elderly': { name: 'Elderly Person', category: 'Healthcare People' },
			'people/pregnant': { name: 'Pregnant Woman', category: 'Healthcare People' },
			'people/baby': { name: 'Baby', category: 'Healthcare People' },
			'people/community_health_worker': { name: 'Community Health Worker', category: 'Healthcare People' },
			'people/emergency_worker': { name: 'Emergency Worker', category: 'Healthcare People' },
			'people/person': { name: 'Person', category: 'Healthcare People' },

			// Medical Specialties
			'specialties/cardiology': { name: 'Cardiology', category: 'Medical Specialties' },
			'specialties/neurology': { name: 'Neurology', category: 'Medical Specialties' },
			'specialties/pediatrics': { name: 'Pediatrics', category: 'Medical Specialties' },
			'specialties/orthopedics': { name: 'Orthopedics', category: 'Medical Specialties' },
			'specialties/radiology': { name: 'Radiology', category: 'Medical Specialties' },
			'specialties/pharmacy': { name: 'Pharmacy', category: 'Medical Specialties' },
			'specialties/psychology': { name: 'Psychology', category: 'Medical Specialties' },
			'specialties/physical_therapy': { name: 'Physical Therapy', category: 'Medical Specialties' },
			'specialties/emergency_department': { name: 'Emergency Department', category: 'Medical Specialties' },
			'specialties/intensive_care': { name: 'Intensive Care', category: 'Medical Specialties' },
		};
	},

	/**
	 * Format icons for select dropdown
	 */
	getIconOptions() {
		const icons = this.getAllIcons();
		return Object.entries(icons).map(([key, icon]) => ({
			label: `${icon.name} (${icon.category})`,
			value: key,
		}));
	},
};

/**
 * AI Helper for Symptoms AI Block
 * Free template-based content generation
 */

class SymptomsAIHelper {
	constructor() {
		this.apiUrl = wpApiSettings.root + '360blocks/v1/generate-symptoms';
		this.cache = new Map();
	}

	/**
	 * Generate symptoms content based on input
	 * @param {string} symptom - The symptom to generate content for
	 * @returns {Promise<string>} - Generated content
	 */
	async generateContent(symptom) {
		if (!symptom || symptom.trim().length === 0) {
			throw new Error('Please enter a symptom to generate content.');
		}

		const normalizedSymptom = symptom.toLowerCase().trim();

		// Check cache first
		if (this.cache.has(normalizedSymptom)) {
			return this.cache.get(normalizedSymptom);
		}

		try {
			const response = await fetch(this.apiUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': window.wpApiSettings?.nonce || '',
				},
				body: JSON.stringify({ symptom: normalizedSymptom }),
			});

			if (!response.ok) {
				throw new Error(`HTTP error! status: ${response.status}`);
			}

			const data = await response.json();

			if (data.success && data.content) {
				// Cache the result
				this.cache.set(normalizedSymptom, data.content);
				return data.content;
			} else {
				throw new Error(data.message || 'Failed to generate content');
			}
		} catch (error) {
			console.error('AI Helper Error:', error);

			// Fallback to basic template
			return this.generateFallbackContent(symptom);
		}
	}

	/**
	 * Generate basic fallback content if API fails
	 * @param {string} symptom - The symptom
	 * @returns {string} - Basic content
	 */
	generateFallbackContent(symptom) {
		return `
<h3>Understanding ${symptom}</h3>

<p>If you're experiencing ${symptom.toLowerCase()}, it's important to understand the potential causes and when to seek medical attention.</p>

<h4>Common Considerations:</h4>
<ul>
    <li>Duration and severity of symptoms</li>
    <li>Associated symptoms or patterns</li>
    <li>Recent activities or changes</li>
    <li>Previous medical history</li>
</ul>

<h4>When to See a Doctor:</h4>
<ul>
    <li>Symptoms persist or worsen</li>
    <li>Severe or sudden onset</li>
    <li>Interfering with daily activities</li>
    <li>Accompanied by other concerning symptoms</li>
</ul>

<div class="medical-disclaimer">
    <p><strong>Medical Disclaimer:</strong> This information is for educational purposes only and should not replace professional medical advice. Always consult with a healthcare provider for proper diagnosis and treatment.</p>
</div>
        `.trim();
	}

	/**
	 * Clear the content cache
	 */
	clearCache() {
		this.cache.clear();
	}

	/**
	 * Get cache statistics
	 * @returns {Object} - Cache stats
	 */
	getCacheStats() {
		return {
			size: this.cache.size,
			keys: Array.from(this.cache.keys()),
		};
	}
}

// Make available globally for the block
window.SymptomsAIHelper = SymptomsAIHelper;

// Initialize default instance
window.symptomsAI = new SymptomsAIHelper();

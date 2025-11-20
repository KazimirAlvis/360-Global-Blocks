import aiHelper from './free-ai-helper.js';

document.addEventListener('alpine:init', () => {
	window.Alpine.data('symptomChecker', aiHelper);
});

document.addEventListener('DOMContentLoaded', () => {
	if (!window.Alpine) {
		console.error('Alpine.js not found. Make sure it is loaded before this script.');
	}
});

const TWO_COLUMN_SELECTOR = '.two-column-slider-container';

function updateTwoColumnSlider(container, targetIndex) {
	if (!container) {
		return;
	}

	const track = container.querySelector('.slide-track');
	if (!track) {
		return;
	}

	const slides = Array.from(track.children);
	const dots = container.querySelectorAll('.dot');
	const total = slides.length;
	if (!total) {
		return;
	}

	let newIndex = typeof targetIndex === 'number' ? targetIndex : parseInt(container.dataset.currentSlide || '0', 10);
	if (Number.isNaN(newIndex)) {
		newIndex = 0;
	}

	newIndex = ((newIndex % total) + total) % total;
	container.dataset.currentSlide = newIndex;
	track.style.transform = `translateX(-${newIndex * 100}%)`;

	slides.forEach((slide, idx) => {
		slide.classList.toggle('active', idx === newIndex);
	});
	dots.forEach((dot, idx) => {
		dot.classList.toggle('active', idx === newIndex);
	});
}

function nextSlide(button) {
	const container = button.closest(TWO_COLUMN_SELECTOR);
	if (!container) {
		return;
	}

	const current = parseInt(container.dataset.currentSlide || '0', 10) || 0;
	updateTwoColumnSlider(container, current + 1);
}

function previousSlide(button) {
	const container = button.closest(TWO_COLUMN_SELECTOR);
	if (!container) {
		return;
	}

	const current = parseInt(container.dataset.currentSlide || '0', 10) || 0;
	updateTwoColumnSlider(container, current - 1);
}

function goToSlide(button, index) {
	const container = button.closest(TWO_COLUMN_SELECTOR);
	if (!container) {
		return;
	}

	updateTwoColumnSlider(container, index);
}

function initSlider(container) {
	const slideContainer = container.querySelector('.slide-container');
	if (!slideContainer || slideContainer.dataset.autoplayInitialized === 'true') {
		return;
	}

	slideContainer.dataset.autoplayInitialized = 'true';
	updateTwoColumnSlider(container, parseInt(container.dataset.currentSlide || '0', 10) || 0);

	if (slideContainer.dataset.autoplay === 'true' && container.querySelectorAll('.slide').length > 1) {
		const autoplaySpeed = container.dataset.autoplaySpeed ? parseInt(container.dataset.autoplaySpeed, 10) : 5000;
		setInterval(() => {
			const nextButton = container.querySelector('.slider-nav.next');
			if (nextButton) {
				nextSlide(nextButton);
				return;
			}
			const current = parseInt(container.dataset.currentSlide || '0', 10) || 0;
			updateTwoColumnSlider(container, current + 1);
		}, autoplaySpeed || 5000);
	}

	const prevButton = container.querySelector('.slider-nav.prev');
	if (prevButton) {
		prevButton.addEventListener('click', () => previousSlide(prevButton));
	}

	const nextButton = container.querySelector('.slider-nav.next');
	if (nextButton) {
		nextButton.addEventListener('click', () => nextSlide(nextButton));
	}

	const dots = container.querySelectorAll('.dot');
	dots.forEach((dot, index) => {
		dot.addEventListener('click', () => goToSlide(dot, index));
	});
}

if (typeof window !== 'undefined') {
	document.addEventListener('DOMContentLoaded', () => {
		const containers = document.querySelectorAll(TWO_COLUMN_SELECTOR);
		containers.forEach(initSlider);
	});
}

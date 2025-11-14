(function () {
	'use strict';

	function activateLiteEmbed(wrapper) {
		if (!wrapper || wrapper.classList.contains('is-active')) {
			return;
		}

		var embedUrl = wrapper.getAttribute('data-embed-url');
		if (!embedUrl) {
			return;
		}

		var autoplayParam = embedUrl.indexOf('?') === -1 ? '?autoplay=1' : '&autoplay=1';
		var finalSrc = embedUrl + autoplayParam;
		var iframe = document.createElement('iframe');
		iframe.setAttribute('src', finalSrc);
		iframe.setAttribute(
			'allow',
			'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture'
		);
		iframe.setAttribute('allowfullscreen', '');
		iframe.setAttribute('loading', 'lazy');
		iframe.setAttribute('frameborder', '0');
		iframe.className = 'youtube-video';

		var title = wrapper.getAttribute('data-title');
		if (title) {
			iframe.setAttribute('title', title);
		}

		wrapper.innerHTML = '';
		wrapper.appendChild(iframe);
		wrapper.classList.add('is-active');
	}

	function bindLiteEmbed(wrapper) {
		if (!wrapper || wrapper.dataset.liteBound === 'true') {
			return;
		}

		wrapper.dataset.liteBound = 'true';

		var triggerClick = function (event) {
			event.preventDefault();
			activateLiteEmbed(wrapper);
		};

		wrapper.addEventListener('click', triggerClick);

		var playButton = wrapper.querySelector('.lite-yt-play');
		if (playButton) {
			playButton.addEventListener('click', triggerClick);
			playButton.addEventListener('keydown', function (event) {
				if (event.key === 'Enter' || event.key === ' ') {
					event.preventDefault();
					activateLiteEmbed(wrapper);
				}
			});
		}
	}

	function initLiteEmbeds() {
		var wrappers = document.querySelectorAll('.video-two-column-block .lite-yt');
		for (var i = 0; i < wrappers.length; i += 1) {
			bindLiteEmbed(wrappers[i]);
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initLiteEmbeds);
	} else {
		initLiteEmbeds();
	}

	window.addEventListener('load', initLiteEmbeds);
})();

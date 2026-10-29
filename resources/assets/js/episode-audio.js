/**
 * Counts plays of WordPress's audio player on episode pages while the global player is off.
 * One play per player and page load is posted to its data-ars-plays-url; the server also deduplicates per visitor.
 */
(function () {
	'use strict';

	// Same formats as player.js: data-ars-plays-one / -other with %s for the number.
	function showPlays(box, count) {
		box.querySelectorAll('[data-ars-plays]').forEach(function (node) {
			var format = (count === 1 ? node.dataset.arsPlaysOne : node.dataset.arsPlaysOther) || '%s';

			node.textContent = format.replace('%s', count.toLocaleString(document.documentElement.lang || undefined));
		});
	}

	function init() {
		document.querySelectorAll('[data-ars-plays-url]').forEach(function (box) {
			var counted = false;

			// "play" does not bubble, so listen while capturing: MediaElement may wrap the <audio> element.
			box.addEventListener('play', function () {
				if (counted) {
					return;
				}

				counted = true;

				fetch(box.dataset.arsPlaysUrl, { method: 'POST', keepalive: true })
					.then(function (response) {
						return response.ok ? response.json() : null;
					})
					.then(function (result) {
						if (result && typeof result.play_count === 'number') {
							showPlays(box, result.play_count);
						}
					})
					.catch(function () {});
			}, true);
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();

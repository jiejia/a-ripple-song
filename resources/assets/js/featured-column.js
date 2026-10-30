/**
 * "Featured" switches in the episode list: saves each change through admin-ajax and reverts it on failure.
 */
(function ($) {
	'use strict';

	var l10n = window.arsFeaturedColumn || {};

	$(document).on('change', '.ars-featured .ars-switch__input', function () {
		var $input = $(this);
		var featured = $input.prop('checked');

		$input.prop('disabled', true);

		$.post(window.ajaxurl, {
			action: l10n.action,
			_ajax_nonce: l10n.nonce,
			post_id: $input.data('postId'),
			featured: featured ? 'yes' : 'no'
		}).then(function (response) {
			return response && response.success ? response : $.Deferred().reject();
		}).done(function (response) {
			$input.prop('checked', response.data.featured);
			wp.a11y.speak(response.data.featured ? l10n.on : l10n.off);
		}).fail(function () {
			$input.prop('checked', !featured);
			wp.a11y.speak(l10n.error, 'assertive');
			window.alert(l10n.error);
		}).always(function () {
			$input.prop('disabled', false);
		});
	});
})(jQuery);

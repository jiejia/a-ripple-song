/**
 * Field library behaviour: media pickers, searchable checkbox lists, repeaters and validation.
 * Events are delegated from document so rows added later work too.
 */
(function ($) {
	'use strict';

	var l10n = window.arsFieldsL10n || {};
	var rowCounter = 0;

	/* Media (file / image) */

	function syncMedia($media) {
		var url = $.trim($media.find('.ars-media__input').val());
		var $image = $media.find('.ars-media__image');
		var $audio = $media.find('.ars-media__audio');

		$media.toggleClass('has-value', url !== '');

		if ($image.length) {
			if ($image.attr('src') !== url) {
				$image.attr('src', url);
			}

			showImageMeta($media);
		}

		if ($audio.length && $audio.attr('src') !== url) {
			if (url) {
				$audio.attr('src', url);
			} else {
				$audio.removeAttr('src');
			}
		}
	}

	// "cover.jpg · 3000 × 3000 px" below the URL; empty until the image has loaded.
	function showImageMeta($media) {
		var image = $media.find('.ars-media__image')[0];
		var $meta = $media.find('.ars-media__meta');
		var url = $.trim($media.find('.ars-media__input').val());

		function render() {
			var name = url.split(/[?#]/)[0].split('/').pop();
			var parts = [];

			try {
				name = decodeURIComponent(name);
			} catch (error) {
				// Keep the raw name when it is not valid percent-encoding.
			}

			if (name) {
				parts.push(name);
			}

			if (image.naturalWidth) {
				parts.push(image.naturalWidth + ' × ' + image.naturalHeight + ' px');
			}

			$meta.text(url ? parts.join(' · ') : '');
		}

		image.onload = render;
		image.onerror = render;
		render();
	}

	// "1:02:03" / "3:25" => seconds.
	function parseDuration(text) {
		var parts = String(text || '').split(':').map(Number);

		if (!text || parts.some(isNaN)) {
			return '';
		}

		return parts.reduce(function (total, part) {
			return total * 60 + part;
		}, 0);
	}

	function attachmentValue(attachment, source) {
		switch (source) {
			case 'duration':
				return parseDuration(attachment.fileLength);
			case 'filesize':
				return attachment.filesizeInBytes || '';
			case 'mime':
				return attachment.mime || '';
			default:
				return attachment[source] !== undefined ? attachment[source] : '';
		}
	}

	// Fill sibling fields from the picked attachment; null clears them.
	function autofill($media, attachment) {
		var map = $media.data('autofill');
		var $scope = $media.closest('.ars-fields');

		if (!map) {
			return;
		}

		$.each(map, function (target, source) {
			$scope.find('[data-ars-key="' + target + '"]').first()
				.val(attachment ? attachmentValue(attachment, source) : '')
				.trigger('change');
		});
	}

	$(document).on('click', '.ars-media__select', function (event) {
		event.preventDefault();

		var $media = $(this).closest('[data-ars-media]');
		var frame = $media.data('arsFrame');

		if (!frame) {
			var library = $media.data('library');

			frame = wp.media({
				title: $media.data('frameTitle'),
				button: { text: $media.data('frameButton') },
				library: library ? { type: library } : {},
				multiple: false
			});

			frame.on('select', function () {
				var attachment = frame.state().get('selection').first().toJSON();

				$media.find('.ars-media__input').val(attachment.url).trigger('change');
				autofill($media, attachment);
			});

			$media.data('arsFrame', frame);
		}

		frame.open();
	});

	$(document).on('click', '.ars-media__remove', function (event) {
		event.preventDefault();

		var $media = $(this).closest('[data-ars-media]');

		$media.find('.ars-media__input').val('').trigger('change');
		autofill($media, null);
	});

	$(document).on('input change', '.ars-media__input', function () {
		syncMedia($(this).closest('[data-ars-media]'));
	});

	$(document).on('change', '.ars-media__input', function () {
		checkImageSize($(this).closest('[data-ars-media]'));
	});

	/* Copy button */

	$(document).on('click', '.ars-copy__button', function (event) {
		event.preventDefault();

		var $button = $(this);
		var $text = $button.find('.ars-copy__text');
		var input = $button.siblings('.ars-input')[0];
		var original = $text.data('original') || $text.text();

		function done() {
			$text.data('original', original).text($button.data('copied'));
			setTimeout(function () {
				$text.text(original);
			}, 1500);
		}

		if (navigator.clipboard && window.isSecureContext) {
			navigator.clipboard.writeText(input.value).then(done);
			return;
		}

		// Fallback for plain-http admin screens.
		input.select();
		document.execCommand('copy');
		done();
	});

	/* Multiselect (checkbox list) */

	$(document).on('change', '.ars-choices input[type="checkbox"]', function () {
		var $choices = $(this).closest('[data-ars-choices]');
		var count = $choices.find('input[type="checkbox"]:checked').length;

		$choices.find('.ars-choices__count').text(String($choices.data('countTemplate')).replace('%d', count));
	});

	$(document).on('input', '.ars-choices__search-input', function () {
		var $choices = $(this).closest('[data-ars-choices]');
		var query = $.trim($(this).val()).toLowerCase();
		var visible = 0;

		$choices.find('.ars-choices__item').each(function () {
			var match = query === '' || $(this).text().toLowerCase().indexOf(query) !== -1;

			$(this).prop('hidden', !match);
			visible += match ? 1 : 0;
		});

		$choices.find('.ars-choices__empty').prop('hidden', visible > 0);
	});

	// Enter in the search box must not submit the surrounding form.
	$(document).on('keydown', '.ars-choices__search-input', function (event) {
		if (event.key === 'Enter') {
			event.preventDefault();
		}
	});

	/* Repeater (complex) */

	function repeaterRows($repeater) {
		return $repeater.children('.ars-repeater__rows').children('.ars-repeater__row');
	}

	function renumberRows($repeater) {
		var label = $repeater.data('rowLabel');
		var titleField = $repeater.data('titleField');
		var $rows = repeaterRows($repeater);

		$rows.each(function (index) {
			var $header = $(this).children('.ars-repeater__row-header');

			$header.find('.ars-repeater__row-index').text(label + ' ' + (index + 1));

			if (titleField) {
				var title = $(this).find('[data-ars-key="' + titleField + '"]').first().val() || '';

				$header.find('.ars-repeater__row-subtitle').text(title);
			}
		});

		$repeater.toggleClass('is-empty', $rows.length === 0);
	}

	function initSortable($repeater) {
		var $list = $repeater.children('.ars-repeater__rows');

		if (!$.fn.sortable || $list.data('ui-sortable')) {
			return;
		}

		$list.sortable({
			handle: '.ars-repeater__handle',
			axis: 'y',
			placeholder: 'ars-repeater__placeholder',
			forcePlaceholderSize: true,
			update: function () {
				renumberRows($repeater);
			}
		});
	}

	$(document).on('click', '.ars-repeater__add', function (event) {
		event.preventDefault();

		var $repeater = $(this).closest('[data-ars-repeater]');
		var template = $repeater.children('.ars-repeater__template').html();
		var key = 'n' + Date.now() + '_' + (rowCounter++);
		var $row = $($.parseHTML($.trim(template.replace(/__INDEX__/g, key))));

		$repeater.children('.ars-repeater__rows').append($row);
		renumberRows($repeater);
		validate();
		$row.find('.ars-input').first().trigger('focus');
	});

	$(document).on('click', '.ars-repeater__remove', function (event) {
		event.preventDefault();

		var $repeater = $(this).closest('[data-ars-repeater]');

		$(this).closest('.ars-repeater__row').remove();
		renumberRows($repeater);
		validate();
	});

	$(document).on('input', '.ars-repeater__row .ars-input', function () {
		var $repeater = $(this).closest('[data-ars-repeater]');

		if ($repeater.data('titleField') === $(this).data('arsKey')) {
			renumberRows($repeater);
		}
	});

	/* Validation */

	var LOCK_KEY = 'ars-invalid-fields';
	var lastNotice = null;

	function isBlockEditor() {
		return $('body').hasClass('block-editor-page') && window.wp && wp.data && wp.data.select('core/editor');
	}

	function fieldIsEmpty($field) {
		if ($field.hasClass('ars-field--multiselect')) {
			return $field.find('input[type="checkbox"]:checked').length === 0;
		}

		if ($field.hasClass('ars-field--complex')) {
			return repeaterRows($field.find('[data-ars-repeater]').first()).length === 0;
		}

		return $.trim($field.find('.ars-input').first().val() || '') === '';
	}

	function fieldError($field) {
		if ($field.hasClass('is-required') && fieldIsEmpty($field)) {
			return l10n.required;
		}

		return $field.data('arsError') || '';
	}

	// Load the image in the browser to measure it; works for media library picks and pasted URLs alike.
	function checkImageSize($media) {
		var rules = $media.data('dimensions');
		var $field = $media.closest('.ars-field');
		var input = $media.find('.ars-media__input')[0];
		var url = $.trim(input.value);

		function setError(message) {
			$field.data('arsError', message);

			if (message) {
				$field.data('touched', true);
			}

			// Classic forms (settings pages) then block submission natively.
			input.setCustomValidity(message);
			validate();
		}

		if (!rules) {
			return;
		}

		if (!url) {
			setError('');
			return;
		}

		var probe = new Image();

		probe.onload = function () {
			if ($.trim(input.value) !== url) {
				return;
			}

			var width = probe.naturalWidth;
			var height = probe.naturalHeight;
			var valid = width >= (rules.min_width || 0)
				&& height >= (rules.min_height || 0)
				&& (!rules.max_width || width <= rules.max_width)
				&& (!rules.max_height || height <= rules.max_height)
				&& (!rules.square || width === height);

			setError(valid ? '' : $media.data('dimensionsMessage') + ' ' + l10n.currentSize.replace('%1$d', width).replace('%2$d', height));
		};

		probe.onerror = function () {
			if ($.trim(input.value) === url) {
				setError(l10n.imageLoadError);
			}
		};

		probe.src = url;
	}

	// The field's own error element, skipping those of sub fields inside repeater rows.
	function ownError($field) {
		return $field.find('.ars-field__error').filter(function () {
			return $(this).closest('.ars-field')[0] === $field[0];
		});
	}

	// Rows inside <template> are not part of the document and are never matched here.
	function validate() {
		var blockEditor = isBlockEditor();
		var invalid = [];

		$('.ars-fields .ars-field').each(function () {
			var $field = $(this);
			var message = fieldError($field);

			if (message) {
				invalid.push($field.data('label'));
			}

			// In the block editor saving gets locked, so show every problem right away.
			if (blockEditor || $field.data('touched')) {
				$field.toggleClass('is-invalid', message !== '');
				ownError($field).text(message);
			}
		});

		if (blockEditor) {
			syncSaveLock(invalid);
		}
	}

	// Block editor meta boxes are saved without native form validation, so lock saving instead.
	function syncSaveLock(invalid) {
		var message = invalid.length ? l10n.invalidNotice.replace('%s', invalid.join(', ')) : '';

		if (message === lastNotice) {
			return;
		}

		lastNotice = message;

		if (message) {
			wp.data.dispatch('core/editor').lockPostSaving(LOCK_KEY);
			wp.data.dispatch('core/notices').createWarningNotice(message, { id: LOCK_KEY, isDismissible: false });
		} else {
			wp.data.dispatch('core/editor').unlockPostSaving(LOCK_KEY);
			wp.data.dispatch('core/notices').removeNotice(LOCK_KEY);
		}
	}

	$(document).on('change focusout', '.ars-fields :input', function () {
		$(this).closest('.ars-field').data('touched', true);
		validate();
	});

	$(document).on('input', '.ars-fields :input', validate);

	// Native validation (classic forms) marks the field before the browser shows its bubble.
	document.addEventListener('invalid', function (event) {
		var $field = $(event.target).closest('.ars-field');

		$field.data('touched', true);
		validate();
	}, true);

	$(function () {
		$('[data-ars-repeater]').each(function () {
			initSortable($(this));
		});

		$('[data-ars-media]').each(function () {
			syncMedia($(this));
		});

		$('[data-ars-media][data-dimensions]').each(function () {
			checkImageSize($(this));
		});

		validate();
	});
})(jQuery);

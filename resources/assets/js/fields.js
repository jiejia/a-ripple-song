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

	function useAttachment($media, attachment) {
		$media.find('.ars-media__input').val(attachment.url).trigger('change');
		autofill($media, attachment);
	}

	// Center the largest selection of the required aspect ratio, like the core site icon cropper.
	function cropSelectOptions(crop) {
		return function (attachment) {
			var width = attachment.get('width');
			var height = attachment.get('height');
			var ratio = crop.width / crop.height;
			var selWidth = width / height > ratio ? Math.round(height * ratio) : width;
			var selHeight = width / height > ratio ? height : Math.round(width / ratio);
			var x1 = Math.round((width - selWidth) / 2);
			var y1 = Math.round((height - selHeight) / 2);

			return {
				aspectRatio: crop.width + ':' + crop.height,
				handles: true,
				keys: true,
				instance: true,
				persistent: true,
				imageWidth: width,
				imageHeight: height,
				x1: x1,
				y1: y1,
				x2: x1 + selWidth,
				y2: y1 + selHeight
			};
		};
	}

	// Pick an image, then crop a copy to the field's aspect ratio through core's crop-image Ajax action.
	function openCropFrame($media, crop) {
		var Cropper = wp.media.controller.Cropper.extend({
			doCrop: function (attachment) {
				var details = attachment.get('cropDetails');
				// Scale down to the target size; never scale up.
				var scale = Math.min(1, crop.width / details.width);

				return wp.ajax.post('crop-image', {
					nonce: attachment.get('nonces').edit,
					id: attachment.get('id'),
					context: 'ars_field_crop',
					cropDetails: $.extend({}, details, {
						dst_width: Math.round(details.width * scale),
						dst_height: Math.round(details.height * scale)
					})
				});
			}
		});
		var library = $media.data('library');
		var frame = wp.media({
			button: { text: l10n.cropButton, close: false },
			states: [
				new wp.media.controller.Library({
					title: $media.data('frameTitle') || l10n.cropTitle,
					library: wp.media.query({ type: library || 'image' }),
					multiple: false,
					date: false,
					suggestedWidth: crop.width,
					suggestedHeight: crop.height
				}),
				new Cropper({ imgSelectOptions: cropSelectOptions(crop) })
			]
		});

		frame.on('select', function () {
			var attachment = frame.state().get('selection').first();
			var ratio = attachment.get('width') / attachment.get('height');

			// Images already in the right shape (within 1%) are used as they are.
			if (Math.abs(ratio / (crop.width / crop.height) - 1) < 0.01) {
				useAttachment($media, attachment.toJSON());
				frame.close();
			} else {
				frame.setState('cropper');
			}
		});

		frame.on('cropped', function (attachment) {
			useAttachment($media, attachment);
		});

		frame.open();
	}

	$(document).on('click', '.ars-media__select', function (event) {
		event.preventDefault();

		var $media = $(this).closest('[data-ars-media]');
		var frame = $media.data('arsFrame');

		// A new frame each time: the cropper state keeps the previous image.
		if ($media.data('crop')) {
			openCropFrame($media, $media.data('crop'));

			return;
		}

		if (!frame) {
			var library = $media.data('library');

			frame = wp.media({
				title: $media.data('frameTitle'),
				button: { text: $media.data('frameButton') },
				library: library ? { type: library } : {},
				multiple: false
			});

			frame.on('select', function () {
				useAttachment($media, frame.state().get('selection').first().toJSON());
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
		checkMedia($(this).closest('[data-ars-media]'));
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

	// Each check reports into its own slot, so one async result never clears another check's error.
	function setMediaError($media, check, message) {
		var $field = $media.closest('.ars-field');
		var input = $media.find('.ars-media__input')[0];
		var errors = $media.data('arsErrors') || {};

		errors[check] = message;
		$media.data('arsErrors', errors);
		message = $.map(errors, function (text) {
			return text || null;
		}).join(' ');

		$field.data('arsError', message);

		if (message) {
			$field.data('touched', true);
		}

		// Classic forms (settings pages) then block submission natively.
		input.setCustomValidity(message);
		validate();
	}

	function checkMedia($media) {
		checkImageSize($media);
		checkFile($media);
	}

	// Load the image in the browser to measure it; works for media library picks and pasted URLs alike.
	function checkImageSize($media) {
		var rules = $media.data('dimensions');
		var input = $media.find('.ars-media__input')[0];
		var url = $.trim(input.value);

		function setError(message) {
			setMediaError($media, 'dimensions', message);
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

	// Matches size_format(): 1 KB = 1024 bytes.
	function formatSize(bytes) {
		return bytes >= 1048576 ? (bytes / 1048576).toFixed(1) + ' MB' : (bytes / 1024).toFixed(1) + ' KB';
	}

	// Bytes read from the start of a file; PNG transparency is declared before the image data.
	var HEAD_BYTES = 65536;

	// Fetch the start of a file and its total size; mirrors FileProbe on the server.
	function readFileHead(url) {
		return fetch(url, { headers: { Range: 'bytes=0-' + (HEAD_BYTES - 1) } }).then(function (response) {
			if (!response.ok) {
				throw new Error(response.status);
			}

			// 206: "bytes 0-65535/830000" holds the total; 200: Content-Length is the whole file.
			var range = /\/(\d+)$/.exec(response.headers.get('Content-Range') || '');
			var size = range ? Number(range[1]) : (response.status === 200 ? Number(response.headers.get('Content-Length')) : 0);
			var declared = (response.headers.get('Content-Type') || '').split(';')[0].trim().toLowerCase();

			return readBytes(response, HEAD_BYTES).then(function (bytes) {
				return { size: size || 0, bytes: bytes, mime: sniffType(bytes) || declared };
			});
		});
	}

	// Stop downloading once enough bytes arrived, in case the server ignored the Range header.
	function readBytes(response, limit) {
		var reader = response.body.getReader();
		var bytes = new Uint8Array(limit);
		var length = 0;

		function pump() {
			return reader.read().then(function (result) {
				if (!result.done) {
					var part = result.value.subarray(0, limit - length);

					bytes.set(part, length);
					length += part.length;
				}

				if (result.done || length >= limit) {
					if (!result.done) {
						reader.cancel();
					}

					return bytes.subarray(0, length);
				}

				return pump();
			});
		}

		return pump();
	}

	function ascii(bytes, start, length) {
		return String.fromCharCode.apply(null, bytes.subarray(start, start + length));
	}

	function sniffType(bytes) {
		if (bytes[0] === 0xFF && bytes[1] === 0xD8 && bytes[2] === 0xFF) {
			return 'image/jpeg';
		}

		if (ascii(bytes, 0, 8) === '\x89PNG\r\n\x1a\n') {
			return 'image/png';
		}

		if (ascii(bytes, 0, 4) === 'GIF8') {
			return 'image/gif';
		}

		if (ascii(bytes, 0, 4) === 'RIFF' && ascii(bytes, 8, 4) === 'WEBP') {
			return 'image/webp';
		}

		return '';
	}

	// An alpha color type (4, 6) or a tRNS chunk; null when the chunks before the image data were not all read.
	function pngHasAlpha(bytes) {
		var view = new DataView(bytes.buffer, bytes.byteOffset, bytes.byteLength);
		var offset;

		if (bytes.length < 26) {
			return null;
		}

		if (bytes[25] === 4 || bytes[25] === 6) {
			return true;
		}

		// Each chunk: 4-byte length, 4-byte type, data, 4-byte CRC.
		for (offset = 8; offset + 8 <= bytes.length; offset += 12 + view.getUint32(offset)) {
			var type = ascii(bytes, offset + 4, 4);

			if (type === 'tRNS') {
				return true;
			}

			if (type === 'IDAT' || type === 'IEND') {
				return false;
			}
		}

		return null;
	}

	// "image/jpeg" => "JPG", like FileProbe::label().
	function typeLabel(mime) {
		var subtype = mime.split('/').pop().toUpperCase();

		return subtype === 'JPEG' ? 'JPG' : subtype;
	}

	// Check type, transparency and size; files on other hosts without CORS are left to the check on save.
	function checkFile($media) {
		var maxSize = $media.data('maxSize');
		var types = $media.data('mimeTypes');
		var opaque = $media.data('opaque') !== undefined;
		var input = $media.find('.ars-media__input')[0];
		var url = $.trim(input.value);

		if (!maxSize && !types && !opaque) {
			return;
		}

		// Drop the previous file's errors; a failed request then leaves the field valid.
		setMediaError($media, 'file', '');

		if (!url || !window.fetch) {
			return;
		}

		readFileHead(url).then(function (file) {
			var errors = [];

			if ($.trim(input.value) !== url) {
				return;
			}

			if (types && file.mime && types.indexOf(file.mime) === -1) {
				errors.push($media.data('mimeTypesMessage') + ' ' + l10n.currentFile.replace('%s', typeLabel(file.mime)));
			}

			if (opaque && file.mime === 'image/png' && pngHasAlpha(file.bytes) === true) {
				errors.push($media.data('opaqueMessage'));
			}

			if (maxSize && file.size > maxSize) {
				errors.push($media.data('maxSizeMessage') + ' ' + l10n.currentFile.replace('%s', formatSize(file.size)));
			}

			setMediaError($media, 'file', errors.join(' '));
		}, function () {
			// Unreadable here (e.g. blocked by CORS); the server checks it on save.
		});
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

	// The "Add New" term form is sent by AJAX and core only clears its own inputs; clear ours once a term is added.
	$(document).ajaxSuccess(function (event, xhr, settings) {
		if (typeof settings.data !== 'string' || settings.data.indexOf('action=add-tag') === -1 || xhr.responseText.indexOf('wp_error') !== -1) {
			return;
		}

		$('#addtag [data-ars-media]').each(function () {
			$(this).find('.ars-media__input').val('').trigger('input');
			$(this).closest('.ars-field').data('touched', false);
		});
	});

	$(function () {
		$('[data-ars-repeater]').each(function () {
			initSortable($(this));
		});

		$('[data-ars-media]').each(function () {
			syncMedia($(this));
		});

		$('[data-ars-media][data-dimensions], [data-ars-media][data-max-size], [data-ars-media][data-mime-types], [data-ars-media][data-opaque]').each(function () {
			checkMedia($(this));
		});

		validate();
	});
})(jQuery);

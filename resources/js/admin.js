( function( $ ) {
	'use strict';

	function arsGetAdminConfig() {
		if ( typeof window.arsPodcastConfig === 'object' && window.arsPodcastConfig ) {
			return window.arsPodcastConfig;
		}

		return {
			i18n: {
				upload: 'Upload',
				remove: 'Remove',
				download: 'Download',
				fileLabel: 'File:',
				selectFile: 'Select file',
				useFile: 'Use this file'
			},
			mediaTypes: {
				audio: 'audio',
				image: 'image',
				transcript: null,
				chapters: 'application'
			}
		};
	}

	function arsSetInputValue( input, value ) {
		if ( ! input ) {
			return;
		}

		var descriptor = Object.getOwnPropertyDescriptor( window.HTMLInputElement.prototype, 'value' );
		if ( descriptor && typeof descriptor.set === 'function' ) {
			descriptor.set.call( input, value );
		} else {
			input.value = value;
		}

		input.dispatchEvent( new Event( 'input', { bubbles: true } ) );
		input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
	}

	function arsFilenameFromUrl( url ) {
		var safe = String( url || '' ).trim();
		if ( ! safe ) {
			return '';
		}

		safe = safe.split( '#' )[ 0 ];
		safe = safe.split( '?' )[ 0 ];

		var parts = safe.split( '/' );
		var last = parts.length ? parts[ parts.length - 1 ] : safe;

		try {
			return decodeURIComponent( last );
		} catch ( e ) {
			return last;
		}
	}

	function arsUpdateMediaState( input, state ) {
		var url = input && input.value ? String( input.value ).trim() : '';
		var fileline = state.fileline;
		var downloadLink = state.downloadLink;
		var removeLink = state.removeLink;
		var nameNode = state.nameNode;
		var imagePreview = state.imagePreview;

		if ( fileline ) {
			fileline.style.display = url ? '' : 'none';
		}

		if ( nameNode ) {
			nameNode.textContent = url ? arsFilenameFromUrl( url ) : '';
		}

		if ( downloadLink ) {
			if ( url ) {
				downloadLink.href = url;
				downloadLink.classList.remove( 'is-disabled' );
				downloadLink.removeAttribute( 'aria-disabled' );
				downloadLink.removeAttribute( 'tabindex' );
			} else {
				downloadLink.href = '#';
				downloadLink.classList.add( 'is-disabled' );
				downloadLink.setAttribute( 'aria-disabled', 'true' );
				downloadLink.setAttribute( 'tabindex', '-1' );
			}
		}

		if ( removeLink ) {
			if ( url ) {
				removeLink.classList.remove( 'is-disabled' );
				removeLink.removeAttribute( 'aria-disabled' );
				removeLink.removeAttribute( 'tabindex' );
			} else {
				removeLink.classList.add( 'is-disabled' );
				removeLink.setAttribute( 'aria-disabled', 'true' );
				removeLink.setAttribute( 'tabindex', '-1' );
			}
		}

		if ( imagePreview ) {
			imagePreview.innerHTML = '';
			if ( url ) {
				var image = document.createElement( 'img' );
				image.src = url;
				image.alt = '';
				imagePreview.appendChild( image );
			}
		}
	}

	function arsBuildMediaUi( input, config ) {
		var mode = input.getAttribute( 'data-ars-media-uploader' ) || 'transcript';
		var isImage = mode === 'image';
		var uploadActions = document.createElement( 'div' );
		uploadActions.className = 'ars-media-url-actions';

		var uploadBtn = document.createElement( 'button' );
		uploadBtn.type = 'button';
		uploadBtn.className = 'button ars-media-url-actions__upload';
		uploadBtn.textContent = config.i18n.upload;
		uploadActions.appendChild( uploadBtn );

		var fileline = document.createElement( 'div' );
		fileline.className = 'ars-media-url-fileline';

		var labelSpan = document.createElement( 'span' );
		labelSpan.className = 'ars-media-url-fileline__label';
		labelSpan.textContent = config.i18n.fileLabel;

		var nameStrong = document.createElement( 'strong' );
		nameStrong.className = 'ars-media-url-fileline__name';

		var downloadLink = document.createElement( 'a' );
		downloadLink.className = 'ars-media-url-fileline__download';
		downloadLink.textContent = config.i18n.download;
		downloadLink.href = '#';
		downloadLink.target = '_blank';
		downloadLink.rel = 'noopener noreferrer';

		var removeLink = document.createElement( 'a' );
		removeLink.className = 'button-link-delete ars-media-url-fileline__remove';
		removeLink.textContent = config.i18n.remove;
		removeLink.href = '#';
		removeLink.setAttribute( 'role', 'button' );

		fileline.appendChild( labelSpan );
		fileline.appendChild( document.createTextNode( ' ' ) );
		fileline.appendChild( nameStrong );
		fileline.appendChild( document.createTextNode( ' (' ) );
		fileline.appendChild( downloadLink );
		fileline.appendChild( document.createTextNode( ' / ' ) );
		fileline.appendChild( removeLink );
		fileline.appendChild( document.createTextNode( ')' ) );

		var imagePreview = null;
		if ( isImage ) {
			imagePreview = document.createElement( 'div' );
			imagePreview.className = 'ars-media-image-preview';
		}

		uploadBtn.addEventListener( 'click', function( e ) {
			e.preventDefault();

			if ( typeof wp === 'undefined' || ! wp.media ) {
				return;
			}

			var libraryType = ( config.mediaTypes && Object.prototype.hasOwnProperty.call( config.mediaTypes, mode ) ) ? config.mediaTypes[ mode ] : null;
			var mediaArgs = {
				title: config.i18n.selectFile,
				button: { text: config.i18n.useFile },
				multiple: false
			};

			if ( libraryType ) {
				mediaArgs.library = { type: libraryType };
			}

			var frame = wp.media( mediaArgs );
			frame.on( 'select', function() {
				var selection = frame.state().get( 'selection' );
				var attachment = selection && selection.first ? selection.first().toJSON() : null;
				if ( attachment && attachment.url ) {
					arsSetInputValue( input, attachment.url );
					arsUpdateMediaState( input, {
						fileline: fileline,
						downloadLink: downloadLink,
						removeLink: removeLink,
						nameNode: nameStrong,
						imagePreview: imagePreview
					} );
				}
			} );
			frame.open();
		} );

		removeLink.addEventListener( 'click', function( e ) {
			e.preventDefault();
			if ( removeLink.classList.contains( 'is-disabled' ) ) {
				return;
			}
			arsSetInputValue( input, '' );
			arsUpdateMediaState( input, {
				fileline: fileline,
				downloadLink: downloadLink,
				removeLink: removeLink,
				nameNode: nameStrong,
				imagePreview: imagePreview
			} );
		} );

		downloadLink.addEventListener( 'click', function( e ) {
			if ( downloadLink.classList.contains( 'is-disabled' ) ) {
				e.preventDefault();
			}
		} );

		input.addEventListener( 'input', function() {
			arsUpdateMediaState( input, {
				fileline: fileline,
				downloadLink: downloadLink,
				removeLink: removeLink,
				nameNode: nameStrong,
				imagePreview: imagePreview
			} );
		} );

		arsUpdateMediaState( input, {
			fileline: fileline,
			downloadLink: downloadLink,
			removeLink: removeLink,
			nameNode: nameStrong,
			imagePreview: imagePreview
		} );

		return {
			uploadActions: uploadActions,
			fileline: fileline,
			imagePreview: imagePreview
		};
	}

	function arsEnhanceMediaFields( root, config ) {
		if ( ! root ) {
			return;
		}

		var inputs = root.querySelectorAll( 'input[data-ars-media-uploader]' );
		inputs.forEach( function( input ) {
			if ( input.dataset.arsMediaEnhanced === '1' ) {
				return;
			}

			var body = input.closest( '.ars-media-field' ) || input.parentElement;
			if ( ! body ) {
				return;
			}

			var existingActions = body.querySelector( '.ars-media-url-actions' );
			var existingFileline = body.querySelector( '.ars-media-url-fileline' );
			if ( existingActions || existingFileline ) {
				input.dataset.arsMediaEnhanced = '1';
				return;
			}

			body.classList.add( 'ars-media-field--enhanced' );
			if ( input.getAttribute( 'data-ars-media-uploader' ) === 'image' ) {
				body.classList.add( 'ars-media-field--image' );
			}

			var ui = arsBuildMediaUi( input, config );
			body.appendChild( ui.uploadActions );
			body.appendChild( ui.fileline );
			if ( ui.imagePreview ) {
				body.appendChild( ui.imagePreview );
			}
			input.dataset.arsMediaEnhanced = '1';
		} );
	}

	function arsEnhanceRepeatables( root ) {
		if ( ! root ) {
			return;
		}

		root.querySelectorAll( '[data-ars-repeatable-field]' ).forEach( function( field ) {
			if ( field.dataset.arsRepeatableEnhanced === '1' ) {
				return;
			}

			field.addEventListener( 'click', function( e ) {
				var addButton = e.target.closest( '[data-ars-repeatable-add]' );
				if ( addButton && field.contains( addButton ) ) {
					var template = field.querySelector( 'template[data-ars-repeatable-template]' );
					var rows = field.querySelector( '[data-ars-repeatable-rows]' );
					if ( template && rows ) {
						var fragment = template.content ? template.content.cloneNode( true ) : null;
						if ( fragment ) {
							rows.appendChild( fragment );
						}
					}
					e.preventDefault();
					return;
				}

				var removeButton = e.target.closest( '[data-ars-repeatable-remove]' );
				if ( removeButton && field.contains( removeButton ) ) {
					var row = removeButton.closest( '.ars-repeatable-field__row' );
					if ( row && row.parentNode ) {
						row.parentNode.removeChild( row );
					}
					e.preventDefault();
				}
			} );

			field.dataset.arsRepeatableEnhanced = '1';
		} );
	}

	/**
	 * Clear native multi-select user controls without requiring keyboard modifiers.
	 */
	function arsEnhanceUserMultiSelects( root ) {
		if ( ! root ) {
			return;
		}

		root.querySelectorAll( '[data-ars-user-multiselect-clear]' ).forEach( function( button ) {
			if ( button.dataset.arsUserMultiselectClearEnhanced === '1' ) {
				return;
			}

			button.addEventListener( 'click', function( e ) {
				var field = button.closest( '.ars-user-multiselect-field' );
				var select = field ? field.querySelector( '[data-ars-user-multiselect]' ) : null;

				if ( select ) {
					Array.prototype.forEach.call( select.options, function( option ) {
						option.selected = false;
					} );

					select.dispatchEvent( new Event( 'change', { bubbles: true } ) );
				}

				e.preventDefault();
			} );

			button.dataset.arsUserMultiselectClearEnhanced = '1';
		} );
	}

	function arsEnhanceAdminForms() {
		var config = arsGetAdminConfig();
		var roots = document.querySelectorAll( '[data-ars-admin-form]' );

		roots.forEach( function( root ) {
			arsEnhanceMediaFields( root, config );
			arsEnhanceRepeatables( root );
			arsEnhanceUserMultiSelects( root );
		} );
	}

	$( function() {
		arsEnhanceAdminForms();
	} );

} )( jQuery );

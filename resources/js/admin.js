(function( $ ) {
	'use strict';

	/**
	 * Enhance Carbon Fields URL inputs to behave like CMB2 "file" fields:
	 * URL input + Upload + Remove + Download.
	 */

	function arsGetAdminConfig() {
		if ( typeof window.arsPodcastAdmin === 'object' && window.arsPodcastAdmin ) {
			return window.arsPodcastAdmin;
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
				transcript: null,
				chapters: 'application'
			},
			metaboxId: 'carbon_fields_container_ars_episode_details'
		};
	}

	function arsSetReactInputValue( input, value ) {
		if ( ! input ) {
			return;
		}

		var setter = Object.getOwnPropertyDescriptor( window.HTMLInputElement.prototype, 'value' );
		if ( setter && typeof setter.set === 'function' ) {
			setter.set.call( input, value );
		} else {
			input.value = value;
		}

		input.dispatchEvent( new Event( 'input', { bubbles: true } ) );
		input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
	}

	function arsUpdateMediaActionsState( input, actionsEl ) {
		var url = ( input && input.value ? String( input.value ).trim() : '' );
		var filelineEl = actionsEl;
		var downloadEl = filelineEl ? filelineEl.querySelector( '.ars-media-url-fileline__download' ) : null;
		var removeEl = filelineEl ? filelineEl.querySelector( '.ars-media-url-fileline__remove' ) : null;
		var nameEl = filelineEl ? filelineEl.querySelector( '.ars-media-url-fileline__name' ) : null;

		if ( filelineEl ) {
			if ( url ) {
				filelineEl.style.display = '';
				if ( nameEl ) {
					nameEl.textContent = arsFilenameFromUrl( url );
				}
			} else {
				filelineEl.style.display = 'none';
				if ( nameEl ) {
					nameEl.textContent = '';
				}
			}
		}

		if ( downloadEl ) {
			if ( url ) {
				downloadEl.href = url;
				downloadEl.classList.remove( 'is-disabled' );
				downloadEl.removeAttribute( 'aria-disabled' );
				downloadEl.removeAttribute( 'tabindex' );
			} else {
				downloadEl.href = '#';
				downloadEl.classList.add( 'is-disabled' );
				downloadEl.setAttribute( 'aria-disabled', 'true' );
				downloadEl.setAttribute( 'tabindex', '-1' );
			}
		}

		if ( removeEl ) {
			if ( url ) {
				removeEl.classList.remove( 'is-disabled' );
				removeEl.removeAttribute( 'aria-disabled' );
				removeEl.removeAttribute( 'tabindex' );
			} else {
				removeEl.classList.add( 'is-disabled' );
				removeEl.setAttribute( 'aria-disabled', 'true' );
				removeEl.setAttribute( 'tabindex', '-1' );
			}
		}
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

	function arsBuildMediaUi( input, config ) {
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

		uploadBtn.addEventListener( 'click', function( e ) {
			e.preventDefault();

			if ( typeof wp === 'undefined' || ! wp.media ) {
				return;
			}

			var mode = input.getAttribute( 'data-ars-media-uploader' ) || 'transcript';
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
					arsSetReactInputValue( input, attachment.url );
					arsUpdateMediaActionsState( input, fileline );
				}
			} );
			frame.open();
		} );

		removeLink.addEventListener( 'click', function( e ) {
			e.preventDefault();
			if ( removeLink.classList.contains( 'is-disabled' ) ) {
				return;
			}
			arsSetReactInputValue( input, '' );
			arsUpdateMediaActionsState( input, fileline );
		} );

		downloadLink.addEventListener( 'click', function( e ) {
			if ( downloadLink.classList.contains( 'is-disabled' ) ) {
				e.preventDefault();
			}
		} );

		input.addEventListener( 'input', function() {
			arsUpdateMediaActionsState( input, fileline );
		} );

		arsUpdateMediaActionsState( input, fileline );
		return {
			uploadActions: uploadActions,
			fileline: fileline
		};
	}

	function arsEnhanceMediaUrlFields( root, config ) {
		if ( ! root ) {
			return;
		}

		var inputs = root.querySelectorAll( 'input[data-ars-media-uploader]' );
		inputs.forEach( function( input ) {
			if ( input.dataset.arsMediaEnhanced === '1' ) {
				return;
			}

			var body = input.closest( '.cf-field__body' ) || input.parentElement;
			if ( ! body ) {
				return;
			}

			if ( body.querySelector( '.ars-media-url-actions' ) || body.querySelector( '.ars-media-url-fileline' ) ) {
				input.dataset.arsMediaEnhanced = '1';
				return;
			}

			body.classList.add( 'ars-media-url-field' );

			var ui = arsBuildMediaUi( input, config );
			body.appendChild( ui.uploadActions );

			var helpEl = body.querySelector( '.cf-field__help' );
			if ( helpEl && helpEl.parentNode ) {
				helpEl.insertAdjacentElement( 'afterend', ui.fileline );
			} else {
				body.appendChild( ui.fileline );
			}

			input.dataset.arsMediaEnhanced = '1';
		} );
	}

	$( function() {
		var config = arsGetAdminConfig();
		var root = document.getElementById( config.metaboxId );
		if ( ! root ) {
			return;
		}

		arsEnhanceMediaUrlFields( root, config );

		var pending = null;
		var observer = new MutationObserver( function() {
			if ( pending ) {
				window.clearTimeout( pending );
			}

			pending = window.setTimeout( function() {
				arsEnhanceMediaUrlFields( root, config );
			}, 50 );
		} );

		observer.observe( root, { subtree: true, childList: true } );
	} );

})( jQuery );

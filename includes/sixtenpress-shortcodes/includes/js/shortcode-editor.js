/*
 * Copyright (c) 2017 Robin Cornett
 */

;(function ( document, $, undefined ) {
	'use strict';

	var SixTenShortcodesEditor = {};

	/**
	 * Initialize. Cycle through the parameters for each shortcode button.
	 */
	SixTenShortcodesEditor.init = function () {

		Object.keys( SixTenShortcodesEditor.params ).forEach( function ( key ) {
			if ( SixTenShortcodesEditor.params.hasOwnProperty( key ) ) {
				var IndividualObject           = SixTenShortcodesEditor.params[key],
					sixtenpress_trigger_target = false,
					sixtenpress_editor_frame   = false,
					modal                      = '.' + IndividualObject.modal,
					inputs                     = $( '#' + IndividualObject.modal ).find( ":input" ),
					defaults                   = _defaults( inputs ),
					button                     = IndividualObject.button.replace( ' ', '.' );

				$( '.' + button ).click( _open );
				$( modal + '.sixtenpress-default-ui .sixtenpress-insert' ).click( _insert );
			}

			/**
			 * Open the modal.
			 */
			function _open( e ) {
				e.preventDefault();

				// Store the trigger target.
				sixtenpress_trigger_target = e.target;
				sixtenpress_editor_frame = true;
				$( modal ).show();

				$( '.media-modal-close, .media-modal-backdrop, .sixtenpress-cancel-insertion' ).click( _hide );
				$( document ).on( 'keydown', function ( e ) {
					if ( 27 === e.keyCode && sixtenpress_editor_frame ) {
						_hide( e );
					}
				} );
			}

			/**
			 * Insert the parsed shortcode into the editor and hide the modal.
			 */
			function _insert( e ) {
				e.preventDefault();
				if ( $( sixtenpress_trigger_target ).hasClass( IndividualObject.button ) ) {
					var string = _getAttributes( inputs, IndividualObject ),
						multi  = IndividualObject.group ? _getMulti( IndividualObject.group, IndividualObject.slug ) : '',
						output = '';
					if ( string ) {
						output = '[' + IndividualObject.shortcode + multi + string + ']';
						if ( ! IndividualObject.self ) {
							output += _getContent( IndividualObject.slug, IndividualObject.modal ) + '[/' + IndividualObject.shortcode + ']';
						}
						tinymce.get( $( sixtenpress_trigger_target ).attr( 'data-editor' ) ).execCommand( 'mceInsertContent', false, output );
					}
				}

				_hide( e );
			}

			/**
			 * Hide the modal.
			 * @param e
			 * @private
			 */
			function _hide( e ) {
				e.preventDefault();
				$( modal ).hide();
				_reset();
				sixtenpress_trigger_target = sixtenpress_editor_frame = false;
			}

			/**
			 * Attempt to reset all inputs to their default state.
			 * @private
			 */
			function _reset() {
				$( inputs ).each( function ( index ) {
					var id   = $( this ).attr( 'id' ),
						type = this.type;
					if ( 'checkbox' === type ) {
						var checked = ( 'checked' === defaults[id] );
						$( this ).attr( 'checked', checked );
					} else {
						$( this ).val( defaults[id] );
					}
				} );

				$( modal + ' .upload-file-preview' ).remove();

				_colorPickers();
				_tinymceClear();
			}

			/**
			 * Clear and reset color pickers.
			 * @private
			 */
			function _colorPickers() {
				var $colorPicker = $( modal ).find( '.wp-picker-container' );

				if ( $colorPicker.length ) {
					$colorPicker.each( function () {
						var $pickerParent = $( this ).parent();
						$pickerParent.html( $pickerParent.find( 'input[type="text"].color-field' ).attr( 'style', '' ) );
						$pickerParent.find( 'input[type="text"].wp-color-picker' ).each( function () {
							var $this    = $( this ),
								settings = $this.data( 'colorpicker' ) || {};
							$this.wpColorPicker( $.extend( {}, false, settings ) );
						} );
					} );
				}
			}

			/**
			 * Clear any tinymce editors.
			 * @private
			 */
			function _tinymceClear() {
				var $id = $( modal ).find( "textarea[id*='" + IndividualObject.slug + "']" );

				if ( $id.length ) {
					var editor = $id.attr( 'id' );
					tinymce.get( editor ).setContent( '' );
				}
			}
		} );
	};

	/**
	 * Get the default values for each input.
	 *
	 * @param inputs
	 * @returns {Array}
	 * @private
	 */
	function _defaults( inputs ) {
		var defaults = [];
		$( inputs ).each( function ( index ) {
			var id = $( this ).attr( 'id' );
			if ( undefined !== id ) {
				defaults[id] = $( this ).val();
			}
			if ( $( this ).is( ':checked' ) ) {
				defaults[id] = 'checked';
			}
		} );
		return defaults;
	}

	/**
	 * Get the shortcode attributes as a string.
	 *
	 * @param inputs
	 * @param object
	 * @returns {string}
	 * @private
	 */
	function _getAttributes( inputs, object ) {
		var output = '';
		$( inputs ).each( function ( index ) {
			var original_id = $( this ).attr( 'id' ),
				value       = $( this ).val(),
				type        = this.type;
			if ( original_id ) {
				var id = original_id.substr( original_id.lastIndexOf( '-' ) + 1 );
				if ( id.includes( 'nonce' ) ) {
					value = '';
				}
				if ( 'checkbox' === type ) {
					if ( object.group.length && id.includes( object.group ) ) {
						value = '';
					} else if ( $( this ).is( ':checked' ) ) {
						var truthy  = [ 1, '1', 'on' ],
							inArray = truthy.indexOf( value );
						value = ( -1 !== inArray ) ? 'true' : value;
					} else if ( $( this ).is( ':required' ) ) {
						value = 'false';
					} else {
						value = '';
					}
				}
				if ( 'button' === type ) {
					value = '';
				}
				if ( 'textarea' === type && ! object.self ) {
					value = '';
				}
				if ( value || $( this ).is( ':required' ) ) {
					output += ' ' + id + '="' + value + '"';
				}
			}
		} );

		return output;
	}

	/**
	 * We assume that a textarea or wysiwg will be content for a
	 * not self-closing shortcode, so values from either of those
	 * will be handled differently and output within the shortcode tags.
	 *
	 * @param slug
	 * @param modal
	 * @private
	 */
	function _getContent( slug, modal ) {
		var $id     = $( '#' + modal ),
			content = $id.find( 'textarea' ).val();
		if ( ! content ) {
			var editor = $id.find( "textarea[id*='" + slug + "']" ).attr( 'id' );
			if ( editor !== 'undefined' && editor.length ) {
				content = tinymce.get( editor ).getContent( {format: 'text'} );
			}
		}
		return content;
	}

	/**
	 * Convert a multi checkbox array to a comma separated string.
	 * Must be defined in the shortcode button args.
	 *
	 * @param fields
	 * @param slug
	 * @returns {string}
	 * @private
	 */
	function _getMulti( fields, slug ) {
		var output = '';
		$( fields ).each( function ( index, value ) {
			var id    = $( '#' + slug ).find( "[id*='" + value + "']" ),
				array = [];
			$( id ).each( function ( index ) {
				if ( $( this ).is( ':checked' ) ) {
					array.push( $( this ).val() );
				}
			} );
			output += array.length ? ' ' + value + '="' + array.toString() + '"' : '';
		} );
		return output;
	}

	SixTenShortcodesEditor.params = typeof SixTenShortcodes === 'undefined' ? '' : SixTenShortcodes;

	if ( typeof SixTenShortcodesEditor.params !== 'undefined' ) {
		SixTenShortcodesEditor.init();
	}

})( document, jQuery );

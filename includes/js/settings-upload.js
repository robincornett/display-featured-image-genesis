;(function ( document, $, undefined ) {
	'use strict';

	var custom_uploader,
	    targetInputClass = '.upload-image-id',
	    previewClass     = 'upload-image-preview',
	    target_input,
	    DFIG             = {};

	DFIG.upload = function () {
		$( '.upload-image' ).on( 'click.upload', _uploadMedia );
		$( '.delete-image' ).on( 'click.delete', _deleteMedia );
		$( '#submit' ).on( 'click.term', _termImages );

		function _uploadMedia( e ) {
			e.preventDefault();
			target_input = $( this ).prev( targetInputClass );

			//If the uploader object has already been created, reopen the dialog
			if ( custom_uploader ) {
				custom_uploader.reset();
			}

			//Extend the wp.media object
			custom_uploader = wp.media.frames.file_frame = wp.media( {
				title: ([DFIG.params.text]),
				button: {
					text: ([DFIG.params.text])
				},
				multiple: false,
				library: {type: 'image'}
			} );

			//When a file is selected, grab the URL and set it as the text field's value
			custom_uploader.on( 'select', function () {

				var attachment   = custom_uploader.state().get( 'selection' ).first().toJSON(),
				    preview      = $( target_input ).prevAll( '.' + previewClass ),
				    deleteButton = $( target_input ).siblings( '.delete-image' ),
				    previewImage = $( '<div />', {
					    class: previewClass
				    } ).append( $( '<img/>', {
					    style: 'max-width:100%;',
					    width: '300px',
					    src: attachment.url,
					    alt: ''
				    } ) );
				$( target_input ).val( attachment.id );
				if ( preview.length ) {
					preview.remove();
				}
				$( target_input ).before( previewImage );
				$( deleteButton ).show();
			} );

			//Open the uploader dialog
			custom_uploader.open();

		}

		function _deleteMedia( e ) {
			e.preventDefault();
			target_input = $( this ).prevAll( targetInputClass );
			var previewView = $( this ).prevAll( '.' + previewClass );

			$( target_input ).val( '' );
			$( previewView ).remove();
			$( this ).hide();
		}

		function _termImages( e ) {
			e.preventDefault();
			var submitButton = $( this ).parentsUntil( '#addtag' ),
			    previewView  = submitButton.siblings( '.term-image-wrap' ).children( '.' + previewClass ),
			    clearInput   = submitButton.siblings( '.term-image-wrap' ).children( targetInputClass );

			if ( $( previewView ).length && $( submitButton ).length ) {
				$( previewView ).delay( 1000 ).fadeOut( 200, function () {
					$( this ).remove();
					$( clearInput ).val( '' );
				} );
			}
		}
	};

	DFIG.params = typeof DisplayFeaturedImageGenesis === 'undefined' ? '' : DisplayFeaturedImageGenesis;
	if ( typeof DFIG.params !== 'undefined' ) {
		DFIG.upload();
	}

})( document, jQuery );

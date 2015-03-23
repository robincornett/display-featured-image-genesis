jQuery(document).ready(function($){

	var custom_uploader;
	var target_input;
	var preview;

	$( '.upload_default_image' ).click(function(e) {

		target_input = $(this).prev( '.upload_image_url' );

		e.preventDefault();

		//If the uploader object has already been created, reopen the dialog
		if (custom_uploader) {
			custom_uploader.open();
			return;
		}

		//Extend the wp.media object
		custom_uploader = wp.media.frames.file_frame = wp.media({
			title: ([objectL10n.text]),
			button: {
				text: ([objectL10n.text])
			},
			multiple: false
		});

		//When a file is selected, grab the URL and set it as the text field's value
		custom_uploader.on('select', function() {

			attachment   = custom_uploader.state().get('selection').first().toJSON();
			preview      = $( target_input ).prevAll( '#upload_logo_preview' );
			previewImage = $( '<div id="upload_logo_preview"><img width="300" src="' + attachment.url + '" /></div>' );
			$( target_input ).val(attachment.id);
			if ( $( preview ).length ) {
				$( preview ).remove();
			}
			$( target_input ).before( previewImage );
		});

		//Open the uploader dialog
		custom_uploader.open();

	});

	$( '.delete_image' ).click( function(e) {

		target_input = $(this).prevAll( '.upload_image_url' );
		previewView  = $(this).prevAll( '#upload_logo_preview' );

		e.preventDefault();

		$( target_input ).val( '' );
		$( previewView ).remove();

	});

});

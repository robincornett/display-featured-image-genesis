/*
 * Copyright (c) 2018 Robin Cornett
 */

;(function ( document, $, undefined ) {
	'use strict';

	var DisplayFeaturedImage = {},
	    classes       = {
		    'wrap': 'displayfeaturedimage-buttons-wrap',
		    'container': 'displayfeaturedimage-wrapper',
		    'dashicon': 'wp-media-buttons-icon dashicons dashicons-camera',
		    'buttons': 'displayfeaturedimagegenesis'
	    };

	DisplayFeaturedImage.init = function () {
		$( '.wp-media-buttons' ).each( _addToggleButton );
		$( '.' + classes.container + ' button' ).on( 'click', _toggleContainer );
	};

	/**
	 * Create and add the toggle button/containers as needed.
	 * @private
	 */
	function _addToggleButton() {
		var $this = $( this ),
		buttons = $( '.' + classes.buttons );
		console.log( buttons );
		if ( 3 > $this.find( buttons ).length ) {
			return;
		}
		var container = $( '<div />', {
			    'class': classes.container
		    } ),
		    button    = $( '<button />', {
			    'text': DisplayFeaturedImage.params.text,
			    'class': 'button show-buttons'
		    } ).prepend( $( '<span />', {
			    'class': classes.dashicon
		    } ) ),
		    wrap      = $( '<div />', {
			    'class': classes.wrap
		    } );
		$this.find( buttons ).wrapAll( wrap );
		$this.find( $( '.' + classes.wrap ) ).wrap( container ).before( button );
	}

	/**
	 * Toggle the container when the button is clicked.
	 *
	 * @param e
	 * @private
	 */
	function _toggleContainer( e ) {
		e.preventDefault();
		jQuery( this ).parents( '.' + classes.container ).find( '.' + classes.wrap ).toggle();
	}

	DisplayFeaturedImage.params = typeof DisplayFeaturedImageVar === 'undefined' ? '' : DisplayFeaturedImageVar;
	if ( typeof DisplayFeaturedImage.params !== 'undefined' ) {
		DisplayFeaturedImage.init();
	}

})( document, jQuery );
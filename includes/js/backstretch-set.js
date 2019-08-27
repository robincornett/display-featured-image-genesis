( function ( document, $, undefined ) {
	'use strict';

	var plugin = {};

	plugin.init = function () {
		_backstretchHandler();
	};

	/********************
	 * Private Functions
	 ********************/

	function _backstretchHandler () {

		var $el = $( '.big-leader' );

		if ( typeof $el === 'undefined' ) {
			return false;
		}

		$el.css( {
			height: ( $( window ).height() ) - ( [ plugin.params.height ] ) + 'px'
		} );

		$el.backstretch( _getSource(), _getArgs() );

		var image = $( '.big-leader .backstretch img' );
		image.attr( 'alt', plugin.params.title ).attr( 'aria-hidden', true );
	}

	/**
	 * Get the image source based on the screen size and available image sizes.
	 */
	function _getSource () {
		if ( plugin.params.slider ) {
			return plugin.params.slider;
		}
		var source = plugin.params.source.backstretch,
			width = window.innerWidth,
			height = $( '.big-leader' ).height();

		if ( plugin.params.source.large && ( plugin.params.width.large >= width && plugin.params.image_height.large >= height ) ) {
			source = plugin.params.source.large;
		}
		if ( plugin.params.source.medium_large && ( plugin.params.width.medium_large >= width && plugin.params.image_height.medium_large >= height ) ) {
			source = plugin.params.source.medium_large;
		}
		return source;
	}

	/**
	 * Get the backstretch parameters.
	 */
	function _getArgs () {
		var args = {
			alignX: plugin.params.alignX,
			alignY: plugin.params.alignY,
			fade: parseInt( plugin.params.fade ),
			scale: 'cover'
		};
		if ( plugin.params.slider ) {
			args.duration = parseInt( plugin.params.duration );
		}

		return args;
	}

	plugin.params = typeof BackStretchVars === 'undefined' ? '' : BackStretchVars;
	if ( typeof plugin.params !== 'undefined' ) {
		plugin.init();
	}

} )( document, jQuery );

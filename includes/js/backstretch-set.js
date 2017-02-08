(function ( document, $, undefined ) {
	'use strict';

	var plugin = {};

	plugin.init = function () {
		_backstretchHandler();
	};

	/********************
	 * Private Functions
	 ********************/

	function _backstretchHandler() {

		var $el = $( '.big-leader' );

		if ( typeof $el === 'undefined' ) {
			return false;
		}

		$el.css( {
			height: ( $( window ).height() ) - ( [plugin.params.height] ) + 'px'
		} );

		$el.backstretch(
			[ _getSource() ], {
				centeredX: Boolean( plugin.params.centeredX ),
				centeredY: Boolean( plugin.params.centeredY ),
				fade: parseInt( plugin.params.fade )
			}
		);

		var image = $( '.big-leader .backstretch img' );
		image.attr( 'alt', plugin.params.title ).attr( 'aria-hidden', true );
	}

	function _getSource() {
		var source = plugin.params.source.backstretch,
			width  = window.innerWidth,
			height = $( '.big-leader' ).height();

		if ( plugin.params.source.large && ( plugin.params.width.large >= width && plugin.params.image_height.large >= height ) ) {
			source = plugin.params.source.large;
		}
		if ( plugin.params.source.medium_large && ( plugin.params.width.medium_large >= width && plugin.params.image_height.medium_large >= height ) ) {
			source = plugin.params.source.medium_large;
		}
		return source;
	}

	$( document ).ready( function () {
		plugin.params = typeof BackStretchVars === 'undefined' ? '' : BackStretchVars;

		if ( typeof plugin.params === 'undefined' ) {
			return;
		}
		plugin.init();
	} );

})( document, jQuery );

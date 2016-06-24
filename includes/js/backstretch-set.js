( function ( document, $, undefined ) {
	'use strict';

	var plugin = {};

	plugin.init = function() {
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
			height: ( $(window).height() ) - ( [ plugin.params.height ] ) + 'px'
		} );

		var source = _getSource();
		$el.backstretch(
			[source], {
				centeredX: Boolean( plugin.params.centeredX ),
				centeredY: Boolean( plugin.params.centeredY ),
				fade: parseInt( plugin.params.fade )
			}
		);

		var image = $( '.big-leader .backstretch img' );
		image.attr( 'alt', plugin.params.title ).attr( 'aria-hidden', true );
	}

	function _getSource() {
		var source = plugin.params.source.backstretch;

		if ( plugin.params.source.large && window.innerWidth <= plugin.params.width.large ) {
			source = plugin.params.source.large;
		}
		if ( plugin.params.source.medium_large && window.innerWidth <= plugin.params.width.medium_large ) {
			source = plugin.params.source.medium_large;
		}
		return source;
	}

	$(document).ready(function () {
		plugin.params = typeof BackStretchVars === 'undefined' ? '' : BackStretchVars;

		if ( typeof plugin.params !== 'undefined' ) {
			plugin.init();
		}
	});

} )( document, jQuery );

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

		var source = window.innerWidth <= plugin.params.width ? plugin.params.largesrc : plugin.params.src,
			$el = $( '.big-leader' );

		if (typeof $el === 'undefined') {
			return false;
		}

		$el.css( {
			height: ( $(window).height() ) - ( [ plugin.params.height ] ) + 'px'
		} );

		$el.backstretch(
			[source], {
				centeredX: '1' === plugin.params.centeredX ? true : false,
				centeredY: '1' === plugin.params.centeredY ? true : false,
				fade: parseInt( plugin.params.fade )
			}
		);
	}

	$(document).ready(function () {
		plugin.params = typeof BackStretchVars === 'undefined' ? '' : BackStretchVars;

		if ( typeof plugin.params !== 'undefined' ) {
			plugin.init();
		}
	});

} )( document, jQuery );

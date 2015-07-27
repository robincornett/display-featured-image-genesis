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
			height: ( $(window).height() ) - ( [plugin.params.height ] ) + 'px'
		} );

		$el.backstretch(
			[source], {
				centeredX: plugin.params.centeredX,
				centeredY: plugin.params.centeredY,
				fade: plugin.params.fade
			}
		);
	}

	$(document).ready(function () {
		plugin.params = typeof BackStretchVars === 'undefined' ? '' : BackStretchVars;

		if ( typeof plugin.params !== 'undefined' ) {
			plugin.init();
		}
	});

} )( this, jQuery );

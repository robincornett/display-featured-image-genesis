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

		var source = plugin.params.source.backstretch,
			$el = $( '.big-leader' );

		if ( typeof $el === 'undefined' ) {
			return false;
		}

		if ( window.innerWidth <= plugin.params.width.large ) {
			source = plugin.params.source.large;
		}
		if ( window.innerWidth <= plugin.params.width.medium_large ) {
			source = plugin.params.source.medium_large;
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

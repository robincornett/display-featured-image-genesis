( function ( document, $, undefined ) {
	'use strict';

	$( '.big-leader' ).css( { 'height': ( $(window).height() ) - ([BackStretchVars.height]) + 'px' });
	$( '.big-leader' ).backstretch(
		[BackStretchVars.src], {
			centeredX: BackStretchVars.centeredX,
			centeredY: BackStretchVars.centeredY,
			fade: BackStretchVars.fade
		}
	);

})( this, jQuery );

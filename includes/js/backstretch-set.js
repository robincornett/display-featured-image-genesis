( function ( document, $, undefined ) {
	'use strict';

	$( '.big-leader' ).css( { 'height': ( $(window).height() ) - ([BackStretchVars.height]) + 'px' });
	$( '.big-leader' ).backstretch( [BackStretchVars.src], { 'fade':750 } );

})( this, jQuery );

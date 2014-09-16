jQuery(document).ready(function($) {
	$(".big-leader").css({'height':($(window).height())+'px'});
	$(".big-leader").backstretch([BackStretchImg.src],{'positionType':'fixed','duration':5000,'fade':750});
});
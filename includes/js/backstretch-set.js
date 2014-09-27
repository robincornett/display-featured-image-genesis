jQuery(document).ready(function($) {

	$(".big-leader").css({'height':($(window).height())-([BackStretchVars.height])+'px'});
	$(".big-leader").backstretch([BackStretchVars.src],{'fade':750});
});
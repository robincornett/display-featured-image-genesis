jQuery(document).ready(function($) {

	$(".big-leader").css({'height':($(window).height())-([HeaderHeight.height])+'px'});
	$(".big-leader").backstretch([BackStretchImg.src],{'positionType':'fixed','fade':750,'centeredY':false});
});
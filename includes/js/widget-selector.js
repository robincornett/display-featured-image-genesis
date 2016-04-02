function term_postback( select_id, taxonomy ) {
	'use strict';
	var data = {
		action: 'widget_selector',
		taxonomy: taxonomy
	};
	jQuery.post(displayfeaturedimagegenesis_ajax_object.ajax_url, data, function(response) {
		// Decode the data received.
		var list = jQuery.parseJSON(response);

		// Keep track of what was previously selected
		var control = jQuery('#' + select_id);
		var old_value = control.val();

		// Clear out the old options, build up the new
		control.empty();
		jQuery.each(list, function(key, value) {
			var new_option = jQuery('<option></option>')
				.attr('value', key).text(value);
			if (value == old_value) {
				new_option.attr('selected', true);
			}
			control.append(new_option);
		});
	});
}

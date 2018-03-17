# Six/Ten Press Shortcodes

This project is meant to be dropped into an existing WordPress plugin or theme and used to allow developers to easily create a button for their shortcode. With fields added to the new shortcode modal, the shortcode will be parsed and inserted into the WordPress editor.

## Installation

To use this, add the entire folder to your plugin/theme project. Include the main file with something like this:

```php
include plugin_dir_path( __FILE__ ) . 'sixtenpress-shortcodes/sixtenpress-shortcodes.php';
```

## Frequently Asked Questions

### How do I get started?

To add a new shortcode button to your editor, you'll need at least two functions: one to register your custom button and one to populate the modal.

To register your custom button, use this code. Some of the parameters are optional, but more information is better than less.

```php
add_action( 'sixtenpress_shortcode_init', 'prefix_register_shortcode_button' );
/**
 * Register the grid shortcode button.
 */
function prefix_register_shortcode_button() {
	sixtenpress_shortcode_register(
		'prefix_my_shortcode', // your actual shortcode
		array(
			'modal'  => 'prefix_custom_shortcode', // this will be used to create a custom ID and class for your modal
			'button' => array(
				'id'       => 'prefix-button', // the unique ID for your custom button
				'class'    => 'prefix-build-shortcode', // unique class, which you may want to include for styling
				'dashicon' => 'dashicons-grid-view', // optional, if you want to use a Dashicon
				'label'    => __( 'Add My Shortcode', 'prefix-textdomain' ), // Custom label for your button
			),
			'self'   => true, // set to false if your shortcode is not self-closing
			'labels' => array(
				'title'  => __( 'Create Shortcode', 'prefix-textdomain' ), // optionally customize the title of the modal window
				'insert' => __( 'Insert Shortcode', 'prefix-textdomain' ), // optionally change the text for the modal insert button
			),
			'group_fields' => array( 'show' ), // optional: use this if you have multi-check fields (groups of checkboxes)
	) );
}
```

Fill your new modal with a great form. There are several ways to approach this--basically, any way you can create a form, do it. Here's an example using CMB2 fields:

```php
add_action( 'sixtenpress_shortcode_modal_prefix_my_shortcode', 'prefix_shortcode_modal' );
/**
 * Add the form to the modal.
 * 
 * @param $shortcode
 */
function prefix_shortcode_modal( $shortcode ) {
	$object = cmb2_get_metabox( prefix_custom_shortcode_config(), 'prefix_custom_shortcode' );
	$form   = '<form class="cmb-form" method="post" id="%1$s" enctype="multipart/form-data" encoding="multipart/form-data"><input type="hidden" name="object_id" value="%2$s">%3$s</form>';
	cmb2_metabox_form( $object, 'prefix-build-shortcode', array(
		'form_format' => $form,
	) );
}

/**
 * Define the fields for the modal CMB2 form.
 * @return array
 */
function prefix_custom_shortcode_config() {
	return array(
		'id'     => 'prefix_custom_shortcode', // Required for CMB2
		'fields' => array(
			array(
				'name'    => __( 'Test Text Small', 'prefix-textdomain' ),
				'desc'    => __( 'field description (optional)', 'prefix-textdomain' ),
				'default' => __( 'default shortcode param value', 'prefix-textdomain' ),
				'id'      => 'shortcode_param',
				'type'    => 'text_small',
			),
		),
	);
}
```

Depending on how your form is built, you may need to also enqueue additional scripts and/or styles.

With this example, your shortcode will be output like this:

[prefix_custom_shortcode shortcode_param="text value"]

### Current Notes and Limitations

If your shortcode form that defaults to `true` and should be included as `false` if it is unchecked, make the checkbox a required field. Otherwise, the attribute is assumed to be optional and will be omitted from the final shortcode.

Currently, CMB2 image fields do not play well in the modal form. The preview fails due to a JavaScript error, and since the form creates both an image and an image_id input, both are passed as attributes, when presumably only the ID is desired.

CMB2 WYSIWYG fields fail completely, with no errors. If content is needed using CMB2, a textarea is currently a better choice.

If an image ID is required, Six/Ten Press image fields in a group will work, as in the Leaven Proofing plugin. Images in groups are saved as ID only.

## Credits
* built by [Robin Cornett](https://robincornett.com)

### Changelog

#### 0.3.8
* fixed: content now copies to editor with formatting

#### 0.3.7
* removed: document ready from JS

#### 0.3.6
* fixed: enqueued styles/scripts check

#### 0.3.5
* reverted: target shortcode buttons back to class (in case of multiples)
* fixed: textarea inputs being skipped in self closing shortcodes

#### 0.3.4
* added: hooks before/after media buttons
* changed: target shortcode buttons by ID instead of class
* improved: modal insert button

#### 0.3.3
* fixed: check for script/style enqueue
* fixed: localized scripts/inline styles output only once

#### 0.3.2
* reduced unnecessary CSS
* tweaked filter for hooks on which to load the modal buttons

#### 0.3.1
* added: filter to restrict media buttons to certain editors

#### 0.3.0
* added: reset inputs to default/original parameters

#### 0.2.0
* change modal show/hide (enables easier implementation of tinyMCE)
* allow required fields (originally, unchecked checkboxes were not passed to output string)
* improve checkbox validation

#### 0.1.1
* fix inline style running multiple times

#### 0.1.0
* initial release

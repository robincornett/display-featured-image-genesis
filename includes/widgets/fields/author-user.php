<?php

$users   = get_users(
	array(
		'capability' => array( 'edit_posts' ),
	)
);
$options = array(
	'' => '--',
);
foreach ( $users as $user ) {
	$options[ $user->ID ] = $user->data->display_name;
}

return array(
	'id'      => 'user',
	'label'   => __( 'Select a user. The email address for this account will be used to pull the Gravatar image.', 'display-featured-image-genesis' ),
	'flex'    => true,
	'choices' => $options,
);

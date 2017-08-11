<?php
	// Important: Check if the file is the one
    // that was registered during the uninstall hook.
	if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
		exit ();

	if ( !current_user_can( 'activate_plugins' ) ) {
        exit ();
	}
    check_admin_referer( 'bulk-plugins' );

	global $wpdb;
	$classes_posts = $wpdb->get_results(
		"SELECT ID FROM $wpdb->posts WHERE post_type = 'easyclass'"
	);
	foreach($classes_posts as $c_post) {
		delete_post_meta($c_post->ID, 'easyclass_color');
		wp_delete_post( $c_post->ID );
	}
	delete_post_meta(0, 'easyclass_search_days');
	delete_post_meta(0, 'easyclass_replace_days');
	$teachers_posts = $wpdb->get_results(
		"SELECT ID FROM $wpdb->posts WHERE post_type = 'teacher'"
	);
	foreach($teachers_posts as $t_post) {
		wp_delete_post( $t_post->ID );
	}
<?php

/*
Load dev only plugins automatically
*/

global $wp_filter;

function hm_load_dev_plugins() {

	$hm_dev_plugins = array(
		'airplane-mode/airplane-mode.php',
		'query-monitor/query-monitor.php',
		'user-switching/user-switching.php',
		'wp-crontrol/wp-crontrol.php'
	);

	foreach ( $hm_dev_plugins as $file ) {
		if ( file_exists( '/dev-plugins/' . $file ) ) {
			require_once $file;

			// run activation hook once
			add_action( 'plugins_loaded', function () use ( $file ) {
				$file = 'dev-plugins/' . plugin_basename( $file );
				if ( is_multisite() ) {
					$activated = get_site_option( "hm_dev_activated_{$file}" );
					$network_wide = true;
				} else {
					$activated = get_option( "hm_dev_activated_{$file}" );
					$network_wide = false;
				}
				if ( ! $activated ) {
					do_action( "activate_{$file}", $network_wide );
					if ( is_multisite() ) {
						update_site_option( "hm_dev_activated_{$file}", true );
					} else {
						update_option( "hm_dev_activated_{$file}", true );
					}
				}
			} );
		}
	}
	unset( $file );

	add_action( 'pre_current_active_plugins', function () use ( $hm_dev_plugins ) {
		global $plugins, $wp_list_table;

		// Add our own mu-plugins to the page
		foreach ( $hm_dev_plugins as $plugin_file ) {
			$plugin_data = get_plugin_data( "/dev-plugins/$plugin_file", false, false ); //Do not apply markup/translate as it'll be cached.

			if ( empty ( $plugin_data['Name'] ) ) {
				$plugin_data['Name'] = $plugin_file;
			}

			$plugins['mustuse'][ $plugin_file ] = $plugin_data;
		}

		// Recount totals
		$GLOBALS['totals']['mustuse'] = count( $plugins['mustuse'] );

		// Only apply the rest if we're actually looking at the page
		if ( $GLOBALS['status'] !== 'mustuse' ) {
			return;
		}

		// Reset the list table's data
		$wp_list_table->items = $plugins['mustuse'];
		foreach ( $wp_list_table->items as $plugin_file => $plugin_data ) {
			$wp_list_table->items[ $plugin_file ] = _get_plugin_data_markup_translate( $plugin_file, $plugin_data, false, true );
		}

		$total_this_page = $GLOBALS['totals']['mustuse'];

		if ( $GLOBALS['orderby'] ) {
			uasort( $wp_list_table->items, array( $wp_list_table, '_order_callback' ) );
		}

		// Force showing all plugins
		// See https://core.trac.wordpress.org/ticket/27110
		$plugins_per_page = $total_this_page;

		$wp_list_table->set_pagination_args( array(
			'total_items' => $total_this_page,
			'per_page'    => $plugins_per_page,
		) );
	} );

	add_action( 'network_admin_plugin_action_links', function ( $actions, $plugin_file, $plugin_data, $context ) use ( $hm_dev_plugins ) {
		if ( $context !== 'mustuse' || ! in_array( $plugin_file, $hm_dev_plugins ) ) {
			return;
		}

		$actions[] = sprintf( '<span style="color:#333">File: <code>%s</code></span>', $plugin_file );

		return $actions;
	}, 10, 4 );

}

$wp_filter = array(
	'muplugins_loaded' => array(
		0 => array(
			'load_dev_plugins' => array(
				'function'      => 'hm_load_dev_plugins',
				'accepted_args' => 0
			)
		)
	)
);
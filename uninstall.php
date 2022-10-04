<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}


// delete option
delete_option('_siga4w_setting');

// delete widget
delete_option('widget_siga4w_widget');


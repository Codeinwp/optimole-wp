<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

$option_name = array('optml-version', 'optml_dismissed_conflicts', 'optml_settings', 'optml_notice_optin');
foreach ( $option_name as $index => $option ) {
	delete_option( $option );
}


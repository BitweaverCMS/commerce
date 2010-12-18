<?php
require_once( '../../../kernel/setup_inc.php' );
require_once( BITCOMMERCE_PKG_PATH.'includes/bitcommerce_start_inc.php' );

if( empty( $entry ) ) {
	$entry = $_REQUEST;
}

if( empty( $entry['country_id'] ) ) {
	$entry['country_id'] = defined( 'STORE_COUNTRY' ) ? STORE_COUNTRY : NULL;
}

if ( !empty( $entry['country_id'] ) ) {
	if( $zones = CommerceCustomer::getCountryZones( $entry['country_id'] ) ) {
		$stateInput = zen_draw_pull_down_menu('state', $zones, '', '', false, TRUE );
	} else {
		$stateInput = zen_draw_input_field('state', zen_get_zone_name($entry['country_id'], $entry['entry_zone_id'], $entry['entry_state']));
	}
} else {
	$stateInput = zen_draw_input_field('state');
}

print $stateInput.'<acronym title="'.tra('Required').'">*</acronym>';

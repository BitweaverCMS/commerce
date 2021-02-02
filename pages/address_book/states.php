<?php
require_once( '../../../kernel/setup_inc.php' );
require_once( BITCOMMERCE_PKG_INCLUDE_PATH.'bitcommerce_start_inc.php' );
require_once( BITCOMMERCE_PKG_INCLUDE_PATH.'functions/html_output.php' );
if( empty( $entry ) ) {
	$entry = $_REQUEST;
}

if( empty( $entry['country_id'] ) ) {
	$entry['country_id'] = defined( 'STORE_COUNTRY' ) ? STORE_COUNTRY : NULL;
}

if ( !empty( $entry['country_id'] ) ) {
	if( !($stateInput = zen_get_country_zone_list('state', $entry['country_id'], (!empty( $entry['entry_zone_id'] ) ? $entry['entry_zone_id'] : '') )) ) { 
		$stateInput = zen_draw_input_field('state', zen_get_zone_name( $entry['country_id'], BitBase::getParameter( $entry, 'entry_zone_id' ), BitBase::getParameter( $entry, 'entry_state' ) ) );
	}
} else {
	$stateInput = zen_draw_input_field('state');
}

print $stateInput;

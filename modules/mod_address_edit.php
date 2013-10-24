<?php
global $gBitCustomer, $gCommerceSystem;

if( empty( $_template->tpl_vars['address']->value['country_id'] ) ) {
	$_template->tpl_vars['address']->value['country_id'] = defined( 'STORE_COUNTRY' ) ? STORE_COUNTRY : NULL;
}

if( $gCommerceSystem->isConfigActive( 'ACCOUNT_STATE' ) ) {
	if ( !empty( $_template->tpl_vars['address']->value['country_id'] ) ) {
		if( !($stateInput = zen_get_country_zone_list('state', $_template->tpl_vars['address']->value['country_id'], (!empty( $_template->tpl_vars['address']->value['entry_zone_id'] ) ? $_template->tpl_vars['address']->value['entry_zone_id'] : '') )) ) { 
			$stateInput = zen_draw_input_field('state', zen_get_zone_name($_template->tpl_vars['address']->value['country_id'], $_template->tpl_vars['address']->value['entry_zone_id'], $_template->tpl_vars['address']->value['entry_state']));
		}
	} else {
		$stateInput = zen_draw_input_field('state');
	}
	$_template->tpl_vars['stateInput'] = new Smarty_Variable( $stateInput );
}

$_template->tpl_vars['countryPullDown'] = new Smarty_Variable( zen_get_country_list('country_id', $_template->tpl_vars['address']->value['country_id'], ' onchange="updateStates(this.value)" ' ) );

if ((isset($_GET['edit']) && ($_SESSION['customer_default_address_id'] != $_GET['edit'])) || (isset($_GET['edit']) == false) ) {
	$gBitSmarty ->assign( 'primaryCheck', TRUE );
}


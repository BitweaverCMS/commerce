<?php

global $gBitThemes;
$gBitThemes->loadJavascript( CONFIG_PKG_PATH.'themes/bootstrap/bootstrap-datepicker/js/bootstrap-datepicker.js');
$gBitThemes->loadCss( CONFIG_PKG_PATH.'themes/bootstrap/bootstrap-datepicker/css/bootstrap-datepicker3.css');

if (DISPLAY_CONDITIONS_ON_CHECKOUT == 'true') {
	if (!isset($_POST['conditions']) || ($_POST['conditions'] != '1')) {
		$messageStack->add_session('checkout_payment', ERROR_CONDITIONS_NOT_ACCEPTED, 'error');
	}
}


foreach( array( 'comments', 'deadline_date' ) as $formKey ) {
	if( !empty( $_POST[$formKey] ) ) {
		$_SESSION[$formKey] = zen_db_prepare_input($_POST[$formKey]);
	}
}

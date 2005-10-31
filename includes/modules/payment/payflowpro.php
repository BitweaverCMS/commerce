<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers                           |
// |                                                                      |
// | http://www.zen-cart.com/index.php                                    |
// |                                                                      |
// | Portions Copyright (c) 2003 Zen Cart                               |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
// $Id: payflowpro.php,v 1.8 2005/10/31 16:19:56 lsces Exp $
//
// JJ: This code really needs cleanup as there's some code that really isn't called at all.
//     I only made enough modifications to make it work with UNIX servers
//     so you are free to a) cleanup the code or b) make it work with Windows :)
//

  class payflowpro {
    var $code, $title, $description, $enabled;

////////////////////////////////////////////////////
// Class constructor -> initialize class variables.
// Sets the class code, description, and status.
////////////////////////////////////////////////////


    function payflowpro() {
      global $order, $messageStack;

      $this->code = 'payflowpro';
     if ($_GET['main_page'] != '') {
       $this->title = tra( 'Credit Card' ); // Payment module title in Catalog
     } else {
       $this->title = tra( 'Verisign PayFlow Pro' ); // Payment module title in Admin
     }
      $this->description = tra( 'Credit Card Test Info:<br /><br />CC#: 4111111111111111 or<br />5105105105105100<br />Expiry: Any' );
      $this->sort_order = MODULE_PAYMENT_PAYFLOWPRO_SORT_ORDER;

      $this->enabled =((MODULE_PAYMENT_PAYFLOWPRO_STATUS == 'True') ? true : false);
//      if (MODULE_PAYMENT_PAYFLOWPRO_SERVEROS =='Linux/Unix' && !function_exists('pfpro_process') ) {
//         $this->title = '<span class="alert">' . tra( 'Verisign PayFlow Pro' ) . '<br />&nbsp;- NOT SUPPORTED</span>';
////         $this->enabled =false; // if pfpro support is not compiled into PHP, do not offer this option.
//// uncomment the above line to simply not display the option if service isn't available in PHP, rather than error message.
//      }

      if ((int)MODULE_PAYMENT_PAYFLOWPRO_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_PAYFLOWPRO_ORDER_STATUS_ID;
      }

      $this->form_action_url = zen_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL', false); // Page to go to upon submitting page info

    }

// class methods
    function update_payflowpro_status() {
      global $order, $gBitDb;

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_PAYFLOWPRO_ZONE > 0) ) {
        $check_flag = false;
        $check = $gBitDb->Execute("select `zone_id` from " . TABLE_ZONES_TO_GEO_ZONES . " where `geo_zone_id` = '" . MODULE_PAYMENT_PAYFLOWPRO_ZONE . "' and `zone_country_id` = '" . $order->billing['country']['id'] . "' order by `zone_id`");
        while (!$check->EOF) {
          if ($check->fields['zone_id'] < 1) {
            $check_flag = true;
            break;
          } elseif ($check->fields['zone_id'] == $order->billing['zone_id']) {
            $check_flag = true;
            break;
          }
          $check->MoveNext();
        }
        if ($check_flag == false) {
          $this->enabled = false;
        }
      }
    }

////////////////////////////////////////////////////
// Javascript form validation
// Check the user input submited on checkout_payment.php with javascript (client-side).
// Examples: validate credit card number, make sure required fields are filled in
////////////////////////////////////////////////////

 function javascript_validation() {
if (MODULE_PAYMENT_PAYFLOWPRO_MODE =='Advanced') {
      $js = '  if (payment_value == "' . $this->code . '") {' . "\n" .
            '    var cc_owner = document.checkout_payment.payflowpro_cc_owner.value;' . "\n" .
            ' var cc_number = document.checkout_payment.payflowpro_cc_number.value;' . "\n" .
                        '         var cc_cvv = document.checkout_payment.payflowpro_cc_csc.value;' . "\n" .
            '    if (cc_owner == "" || cc_owner.length < ' . CC_OWNER_MIN_LENGTH . ') {' . "\n" .
            '      error_message = error_message + "' . tra( '* The credit card number must be at least ' . CC_NUMBER_MIN_LENGTH . ' characters.\n' ) . '";' . "\n" .
            '      error = 1;' . "\n" .
            '    }' . "\n" .
            '    if (cc_number == "" || cc_number.length < ' . CC_NUMBER_MIN_LENGTH . ') {' . "\n" .
            '      error_message = error_message + "' . tra( '* The credit card number must be at least ' . CC_NUMBER_MIN_LENGTH . ' characters.\n' ) . '";' . "\n" .
            '      error = 1;' . "\n" .
            '    }' . "\n" .
                        '         if (cc_cvv == "" || cc_cvv.length < "3") {' . "\n".
                        '           error_message = error_message + "' . tra( '* You must enter the 3 or 4 digit number on the back of your credit card\n' ) . '";' . "\n" .
                        '           error = 1;' . "\n" .
                        '         }' . "\n" .
            '  }' . "\n";

      return $js;
    } else {
      return false;
                }
    }
////////////////////////////////////////////////////
// !Form fields for user input
// Output any required information in form fields
// Examples: ask for extra fields (credit card number), display extra information
////////////////////////////////////////////////////



    // Display Credit Card Information Submission Fields on the Checkout Payment Page
    function selection() {
      global $order;

      for ($i=1; $i<13; $i++) {
        $expires_month[] = array('id' => sprintf('%02d', $i), 'text' => strftime('%B',mktime(0,0,0,$i,1,2000)));
      }

      $today = getdate();
      for ($i=$today['year']; $i < $today['year']+10; $i++) {
        $expires_year[] = array('id' => strftime('%y',mktime(0,0,0,1,1,$i)), 'text' => strftime('%Y',mktime(0,0,0,1,1,$i)));
      }
      $selection = array('id' => $this->code,
                         'module' => $this->title,
                         'fields' => array(array('title' => tra( 'Card Owner\'s Name:' ),
                                                 'field' => zen_draw_input_field('payflowpro_cc_owner', $order->billing['firstname'] . ' ' . $order->billing['lastname'])),
                                           array('title' => tra( 'Card Number:' ),
                                                 'field' => zen_draw_input_field('payflowpro_cc_number')),
                                           array('title' => tra( 'Expiration Date:' ),
                                                 'field' => zen_draw_pull_down_menu('payflowpro_cc_expires_month', $expires_month) . '&nbsp;' . zen_draw_pull_down_menu('payflowpro_cc_expires_year', $expires_year)),
                                           array('title' => tra( 'CVV Number' ) . ' ' . '<a href="javascript:popupWindow(\'' . zen_href_link(FILENAME_POPUP_CVV_HELP) . '\')">' . tra( ' (<a href="javascript:popupWindow(\'' . zen_href_link(FILENAME_POPUP_CVV_HELP) . '\')">' . 'More Info' . '</a>)' ) . '</a>',
                                                 'field' => zen_draw_input_field('payflowpro_cc_csc','','SIZE=4, MAXLENGTH=4'))));

//      if (MODULE_PAYMENT_PAYFLOWPRO_SERVEROS =='Linux/Unix' && !function_exists('pfpro_process') ) {
//          $selection['fields']= array(); // remove the data-entry fields if service can't be used.
//      }

      return $selection;
}

////////////////////////////////////////////////////
// Pre confirmation checks (ie, check if credit card
// information is right before sending the info to
// the payment server
////////////////////////////////////////////////////

    function pre_confirmation_check() {
      global $_POST;

      include(DIR_WS_CLASSES . 'cc_validation.php');
		$result = FALSE;
		if( empty( $_POST['payflowpro_cc_number'] ) ) {
			$error = tra( 'Please enter a credit card number.' );
		} else {
      $cc_validation = new cc_validation();
      $result = $cc_validation->validate($_POST['payflowpro_cc_number'], $_POST['payflowpro_cc_expires_month'], $_POST['payflowpro_cc_expires_year'], $_POST['payflowpro_cc_csc']);

      $error = '';
      switch ($result) {
        case -1:
          $error = sprintf(TEXT_CCVAL_ERROR_UNKNOWN_CARD, substr($cc_validation->cc_number, 0, 4));
          break;
        case -2:
        case -3:
        case -4:
          $error = TEXT_CCVAL_ERROR_INVALID_DATE;
          break;
        case false:
          $error = TEXT_CCVAL_ERROR_INVALID_NUMBER;
          break;
      }
		}

      if ( ($result == false) || ($result < 1) ) {
        $payment_error_return = 'payment_error=' . $this->code . '&error=' . urlencode($error) . '&payflowpro_cc_owner=' . urlencode($_POST['payflowpro_cc_owner']) . '&payflowpro_cc_expires_month=' . $_POST['payflowpro_cc_expires_month'] . '&payflowpro_cc_expires_year=' . $_POST['payflowpro_cc_expires_year'];
        zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false));
      }

      $this->cc_card_type = $cc_validation->cc_type;
      $this->cc_card_number = $cc_validation->cc_number;
      $this->cc_expiry_month = $cc_validation->cc_expiry_month;
      $this->cc_expiry_year = $cc_validation->cc_expiry_year;
}

////////////////////////////////////////////////////
// Functions to execute before displaying the checkout
// confirmation page
////////////////////////////////////////////////////

    function confirmation() {
      global $_POST;

      $confirmation = array('title' => $this->title . ': ' . $this->cc_card_type,
                            'fields' => array(array('title' => tra( 'Card Owner:' ),
                                                    'field' => $_POST['payflowpro_cc_owner']),
                                              array('title' => tra( 'Card Number:' ),
                                                    'field' => substr($this->cc_card_number, 0, 4) . str_repeat('X', (strlen($this->cc_card_number) - 8)) . substr($this->cc_card_number, -4)),
                                              array('title' => tra( 'Expiration Date:' ),
                                                    'field' => strftime('%B,%Y', mktime(0,0,0,$_POST['payflowpro_cc_expires_month'], 1, '20' . $_POST['payflowpro_cc_expires_year']))),
                                              array('title' => tra( 'CVV Number' ),
                                                'field' => $_POST['payflowpro_cc_csc'])));

      return $confirmation;
}
////////////////////////////////////////////////////
// Functions to execute before finishing the form
// Examples: add extra hidden fields to the form
////////////////////////////////////////////////////

    function process_button() {
      global $_SERVER, $_POST, $order, $total_tax, $shipping_cost, $customer_id;
      // These are hidden fields on the checkout confirmation page
	  $process_button_string = zen_draw_hidden_field('cc_owner', $_POST['payflowpro_cc_owner']) .
                               zen_draw_hidden_field('cc_expires', $this->cc_expiry_month . substr($this->cc_expiry_year, -2)) .
                               zen_draw_hidden_field('cc_type', $this->cc_card_type) .
                               zen_draw_hidden_field('cc_number', $this->cc_card_number) .
                               zen_draw_hidden_field('cc_cvv', $_POST['payflowpro_cc_csc']);

      $process_button_string .= zen_draw_hidden_field(zen_session_name(), zen_session_id());
      return $process_button_string;
}


////////////////////////////////////////////////////
// Test Credit Card# 4111111111111111
// Expiration any date after current date.
// Functions to execute before processing the order
// Examples: retreive result from online payment services
////////////////////////////////////////////////////

    function before_process() {
		global $_GET, $messageStack, $gDebug, $_POST, $response, $gBitDb, $gBitUser, $order;

		$order->info['cc_number'] = $_POST['cc_number'];
		$order->info['cc_expires'] = $_POST['cc_expires'];
		$order->info['cc_type'] = $_POST['cc_type'];
		$order->info['cc_owner'] = $_POST['cc_owner'];
		$order->info['cc_cvv'] = $_POST['cc_cvv'];
		// Calculate the next expected order id
		$last_order_id = $gBitDb->Execute("select * from " . TABLE_ORDERS . " order by `orders_id` desc", NULL, 1);
		$new_order_id = $last_order_id->fields['orders_id'];
		$new_order_id = ($new_order_id + 1);

		 $parmList .= "TRXTYPE=" . ((MODULE_PAYMENT_PAYFLOWPRO_TYPE == 'Authorization') ? 'A' : 'S');
		 $parmList .= "&TENDER=C";
		 $parmList .= "&PWD=" . MODULE_PAYMENT_PAYFLOWPRO_PWD;
		 $parmList .= "&USER=" . MODULE_PAYMENT_PAYFLOWPRO_LOGIN;
		 $parmList .= "&VENDOR=" . MODULE_PAYMENT_PAYFLOWPRO_LOGIN;
		 $parmList .= "&PARTNER=" . MODULE_PAYMENT_PAYFLOWPRO_PARTNER;

		 $parmList .= "&ZIP=".$order->customer['postcode'];
		 $parmList .= "&COMMENT1=" . 'CustID:' . $_SESSION['customer_id'] . ' OrderID:' . $new_order_id . ' Email:'. $order->customer['email_address'];
		 $parmList .= "&COMMENT2=" . 'ZenSessName:' . zen_session_name() . ' ZenSessID:' . zen_session_id() ;
		 if (MODULE_PAYMENT_PAYFLOWPRO_MODE =='Test') $parmList .= ' -- PHP/COM Test Transaction --';
		 $parmList .= "&ACCT=" . $order->info['cc_number'];
		 $parmList .= "&EXPDATE=" . $order->info['cc_expires'];
		 $parmList .= "&CVV2=" . $order->info['cc_cvv'];
		 $parmList .= "&AMT=" . number_format($order->info['total'], 2,'.','');
		 $parmList .= "&NAME=" . $order->billing['firstname'] . ' ' . $order->billing['lastname'];
		 $parmList .= "&STREET=" . $order->customer['street_address'];

	     if (MODULE_PAYMENT_PAYFLOWPRO_MODE =='Test') {
	       $url="test-payflow.verisign.com";
	     } else {
	       $url="payflow.verisign.com";
	     }

		if (MODULE_PAYMENT_PAYFLOWPRO_SERVEROS=='Windows') { // for Windows servers only
			$objCOM = new COM("PFProCOMControl.PFProCOMControl.1");
			$ctx1 = $objCOM->CreateContext($url, 443, 30, "", 0, "", "");
			$result = $objCOM->SubmitTransaction($ctx1, $parmList, strlen($parmList));
			$objCOM->DestroyContext($ctx1);

	    } else {  // end Windows version

		    $parmList = str_replace('"','~',$parmList);

		   // The following method requires that the "pfpro" components be compiled into PHP on your server.
		   // Detailed information on the compiling process is contained here:  http://www.php.net/manual/en/ref.pfpro.php
		    $transaction = array(USER=> MODULE_PAYMENT_PAYFLOWPRO_LOGIN,
	                 PWD => MODULE_PAYMENT_PAYFLOWPRO_PWD,
	                 VENDOR=> MODULE_PAYMENT_PAYFLOWPRO_LOGIN,
	                 PARTNER=> MODULE_PAYMENT_PAYFLOWPRO_PARTNER,
	                 TRXTYPE => ((MODULE_PAYMENT_PAYFLOWPRO_TYPE == 'Authorization') ? 'A' : 'S'),
	                 TENDER=> 'C',
	                 ZIP=> $order->customer['postcode'],
	                 COMMENT1=> 'CustID:' . $_SESSION['customer_id'] . '+OrderID:' . $new_order_id . '+Email:'. $order->customer['email_address'],
	                 COMMENT2=> 'ZenSessName:' . zen_session_name() . '+ZenSessID:' . zen_session_id() .
	                      (MODULE_PAYMENT_PAYFLOWPRO_MODE =='Test') ? '+++Test Transaction+++' : '',
	                 ACCT=> $order->info['cc_number'],
	                 EXPDATE=> $order->info['cc_expires'],
	                 CVV2=> $order->info['cc_cvv'],
	                 AMT=> number_format($order->info['total'], 2,'.',''),
	                 NAME=> $order->billing['firstname'] . ' ' . $order->billing['lastname'],
	                 STREET => $order->customer['street_address']
				);
			putenv("LD_LIBRARY_PATH=".getenv("LD_LIBRARY_PATH").":".DIR_FS_CATALOG."includes/modules/payment/payflowpro");
			putenv("PFPRO_CERT_PATH=".MODULES_PAYMENT_PAYFLOW_PRO_CERT_PATH);
			$resultcodes=exec(DIR_FS_CATALOG.'includes/modules/payment/payflowpro/pfpro '.$url. ' 443 "'.$parmList.'" 30  2>&1', $output, $return_value);

			$resultStrings  = explode( '&', $resultcodes );
			$codeHash = array();
			foreach( $resultStrings as $s ) {
				list($key, $val) = explode( '=', $s, 2 );
				$codeHash[$key] = $val;
			}

			//debug code
			if( $gDebug ){
				echo "calling exec " . (DIR_FS_CATALOG.'includes/modules/payment/bin/pfpro '.$url. ' 443 "'.$parmList.'" 30  2>&1')."<BR>\n";
				echo "RESULTS:<BR>\n";
				print_r($resultcodes);
				echo "<BR>\n";
				exit;
			}

				//$debug='ON';
			list($strA, $strB) = split ('[|]', $resultcodes);
			if ($debug=='ON') $messageStack->add_session("valueA: " . $strA,'error');
			if ($debug=='ON') $messageStack->add_session("valueB: " . $strB,'error');
			if ($debug=='ON' || (zen_not_null($return_value) && $return_value!='0')) $messageStack->add_session('Result code: '.$return_value, 'caution');
			if ($debug=='ON') foreach($output as $key=>$value) {$messageStack->add_session("$key => $value<br />",'caution'); }
			exec("exit");

			$return = '&'.$output[0].'&';

			# Check result
			if( isset( $codeHash['PNREF'] ) ) {
				$this->pnref = $codeHash['PNREF'];
			}

			if( isset( $codeHash['RESULT'] ) ) {
				$this->result = $codeHash['RESULT'];
			}

			while (list ($key, $val) = each ($output)) {
				$result_list .= $key.'='.urlencode($val).'&';
			}

			$this->result_list = $result_list;

			if( MODULE_PAYMENT_PAYFLOWPRO_CARD_PRIVACY == 'True' ) {
				//replace middle CC num with XXXX
				$order->info['cc_number'] = substr($_POST['cc_number'], 0, 4) . str_repeat('X', (strlen($_POST['cc_number']) - 8)) . substr($_POST['cc_number'], -4);
			}


			$message .= DIR_FS_CATALOG.'includes/modules/payment/bin/pfpro '.$url. ' 443 "'.$parmList.'" 30 ';
			$message .= $url ."\n";
			$message .= $this->result ."\n";
			$message .= $result_list ."\n";
			$message .= $this->pnref ."\n";
			$message .= $return ."\n";
			if ($debug=='ON') {
				zen_mail(STORE_NAME.'payflow pro debug - pre pfpro_process()', EMAIL_FROM, 'payflow pro debug codes' , $message, STORE_NAME, EMAIL_FROM);
			}
			//if ($result['RESULT'] != "0")
			if ($this->result != "0") {
				$gBitDb->RollbackTrans();
				$gBitDb->query( "insert into " . TABLE_PUBS_CREDIT_CARD_LOG . " (orders_id, customers_id, ref_id, trans_result,trans_auth_code, trans_message, trans_amount, trans_date) values ( NULL, ?, ?, ?, '-', ?, ?, 'NOW' )", array(  $gBitUser->mUserId, $this->pnref, $this->result, 'failed for cust_id: '.$gBitUser->mUserId.' - '.$order->customer['email_address'].':'.$codeHash['RESPMSG'], number_format($order->info['total'], 2,'.','') ) );
				$messageStack->add_session('checkout_payment',tra( 'There has been an error processing you credit card, please try again.' ).'<br/>'.$codeHash['RESPMSG'],'error');
				zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode( tra( 'There has been an error processing you credit card, please try again.' ) ), 'SSL', true, false));
			}
		}//End of if Not Windows (else)
}



//This function does nothing but just to hold code to demonstrate how you can use payflowpro
//using the pfpro_process() call instead of the exec method. The pfpro_process method should
//be better but I haven't debuged it.
function placeholder_for_so_call(){

	//if ($debug=='ON') die ("stopping here");
		if (false) {
		   // Uncomment the next line if PHP isn't loading the pfpro.so extension
		   // dl("pfpro.so");

		   // Fix this line according to where your payflow pro cert may be found
		   // (this is the cert included in the VeriSign SDK, at
		   // verisign/payflowpro/whatever/certs)
		   //pfpro_init();
	}
								   $result = pfpro_process($transaction, $url);

								   if (!$result) {
								     die("Couldn't establish link to Verisign.\n");
								   }
								   //pfpro_cleanup();
								// PROCESS RESULTS:
	 if (false) {
		$result = trim($_GET['RESULT']) ;
		$responsemsg = $_GET['RESPMSG'] ;
		while (list ($key, $val) = each ($_GET)) {
			$getparameters .= $key.'='.urlencode($val).'&';
		}

	 } // endif false

	if ($debug=='ON') $messageStack->add_session("RESULT:"  . $result['RESULT'],'error');
	 if ($debug=='ON')  zen_mail(STORE_NAME.'payflow pro debug', EMAIL_FROM, 'payflow pro debug codes' , $message, STORE_NAME, EMAIL_FROM);

}


	function after_process() {
		global $insert_id, $order, $gBitDb, $gBitUser, $result;
		$gBitDb->Execute("insert into " . TABLE_ORDERS_STATUS_HISTORY . " (comments, orders_id, orders_status_id, `date_added`) values ('Credit Card processed', '". (int)$insert_id . "','1', 'NOW' )");
		$gBitDb->query("insert into " . TABLE_PUBS_CREDIT_CARD_LOG . " (orders_id, customers_id, ref_id, trans_result,trans_auth_code, trans_message, trans_amount,trans_date) values ( ?, ?, ?, ?,'-', ?, ?, 'NOW' )", array( $insert_id, $gBitUser->mUserId, $this->pnref, $this->result, 'success for cust_id:'.$order->customer['email_address'].":".urldecode( $this->result_list ), number_format( $order->info['total'], 2, '.', '' ) ) );
		return false;
	}


////////////////////////////////////////////////////
// If an error occurs with the process, output error messages here
////////////////////////////////////////////////////

    function get_error() {
      global $_GET;

      $error = array('title' => tra( 'There has been an error processing you credit card, please try again.' ),
                     'error' => stripslashes(urldecode($_GET['error'])));

      return $error;
    }

////////////////////////////////////////////////////
// Check if module is installed (Administration Tool)
// TABLES: configuration
////////////////////////////////////////////////////


    function check() {
      global $gBitDb;
      if (!isset($this->_check)) {
        $check_query = $gBitDb->Execute("select `configuration_value` from " . TABLE_CONFIGURATION . " where `configuration_key` = 'MODULE_PAYMENT_PAYFLOWPRO_STATUS'");
        $this->_check = $check_query->RecordCount();
      }
      return $this->_check;
    }

////////////////////////////////////////////////////
// Install the module (Administration Tool)
// TABLES: configuration
////////////////////////////////////////////////////

 function install() {
  global $gBitDb;
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Enable PayFlow Pro Module', 'MODULE_PAYMENT_PAYFLOWPRO_STATUS', 'True', 'Do you want to accept PayFlow Pro payments?', '6', '1', 'zen_cfg_select_option(array(\'True\', \'False\'), ', 'NOW')");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('PayFlow Pro Login', 'MODULE_PAYMENT_PAYFLOWPRO_LOGIN', 'login', 'Your case-sensitive login that you defined at registration.', '6', '2', 'NOW')");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('PayFlow Pro Password', 'MODULE_PAYMENT_PAYFLOWPRO_PWD', 'password', 'Your case-sensitive password that you defined at registration.', '6', '3', 'NOW')");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('PayFlow Pro Activation Mode', 'MODULE_PAYMENT_PAYFLOWPRO_MODE', 'Test', 'What mode is your account in?<br><em>Test Accounts:</em><br>Visa:4111111111111111<br>MC: 5105105105105100<br><li><b>Live</b> = Activated/Live.</li><li><b>Test</b> = Test Mode</li>', '6', '4', 'zen_cfg_select_option(array(\'Live\', \'Test\'), ', 'NOW')");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Transaction Method', 'MODULE_PAYMENT_PAYFLOWPRO_TYPE', 'Authorization', 'Transaction method used for processing orders', '6', '5', 'zen_cfg_select_option(array(\'Authorization\', \'Sale\'), ', 'NOW')");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('PayFlow Partner ID', 'MODULE_PAYMENT_PAYFLOWPRO_PARTNER', 'PartnerId', 'VeriSign Your partner ID is provided to you by the authorized VeriSign Reseller who signed you up for the PayFlow service. If you signed up yourself, use <em>VeriSign</em>.', '6', '6', 'NOW')");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Sort order of display.', 'MODULE_PAYMENT_PAYFLOWPRO_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '7', 'NOW')");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `use_function`, `set_function`, `date_added`) values ('Payment Zone', 'MODULE_PAYMENT_PAYFLOWPRO_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '8', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', 'NOW')");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `use_function`, `date_added`) values ('Set Order Status', 'MODULE_PAYMENT_PAYFLOWPRO_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '9', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', 'NOW')");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Credit Card Privacy', 'MODULE_PAYMENT_PAYFLOWPRO_CARD_PRIVACY', 'True', 'Replace the middle digits of the credit card with XXXX? You will not be able to retrieve the original card number.', '6', '10', 'zen_cfg_select_option(array(\'True\', \'False\'), ', 'NOW')");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Host Server OS', 'MODULE_PAYMENT_PAYFLOWPRO_SERVEROS', 'Linux/Unix', 'Choose your server OS. <br>To use <strong>Linux/Unix</strong>, you need to compile the --with-pfpro support into PHP from the Verisign SDK.<br />To use <strong>Windows</strong>, you need to install the COM objects from the Verisign SDK.', '6', '11', 'zen_cfg_select_option(array(\'Linux/Unix\', \'Windows\'), ', 'NOW')");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('PayFlow Pro Certificate Path', 'MODULES_PAYMENT_PAYFLOW_PRO_CERT_PATH', '" . DIR_FS_CATALOG . "includes/modules/payment/payflowpro', 'What is the full path to your PFPRO CERT files?<br />Sometimes is: /usr/local/payflowpro/certs<br />but depends on your host.', '6', '12', 'NOW')");
    }

////////////////////////////////////////////////////
// Remove the module (Administration Tool)
// TABLES: configuration
////////////////////////////////////////////////////

    function remove() {
     global $gBitDb;
      $gBitDb->Execute("delete from " . TABLE_CONFIGURATION . " where `configuration_key` in ('" . implode("', '", $this->keys()) . "')");
    }
////////////////////////////////////////////////////
// Create our Key - > Value Arrays
////////////////////////////////////////////////////
    function keys() {
      return array('MODULE_PAYMENT_PAYFLOWPRO_STATUS', 'MODULE_PAYMENT_PAYFLOWPRO_LOGIN', 'MODULE_PAYMENT_PAYFLOWPRO_PWD', 'MODULE_PAYMENT_PAYFLOWPRO_MODE', 'MODULE_PAYMENT_PAYFLOWPRO_TYPE', 'MODULE_PAYMENT_PAYFLOWPRO_PARTNER', 'MODULE_PAYMENT_PAYFLOWPRO_SORT_ORDER', 'MODULE_PAYMENT_PAYFLOWPRO_ZONE', 'MODULE_PAYMENT_PAYFLOWPRO_ORDER_STATUS_ID', 'MODULE_PAYMENT_PAYFLOWPRO_CARD_PRIVACY', 'MODULE_PAYMENT_PAYFLOWPRO_SERVEROS', 'MODULES_PAYMENT_PAYFLOW_PRO_CERT_PATH');


    }
  }













#
# This function creates a temporary file and store some data in it
# It will return filename if successful and "false" if it fails.
#
function func_temp_store($data) {
	$tmpfile = @tempnam(DIR_FS_SQL_CACHE,"zentmp");
	if (empty($tmpfile)) return false;
	$fp = @fopen($tmpfile,"w");
	if (!$fp) {
		@unlink($tmpfile);
		return false;
	}
	fwrite($fp,$data);
	fclose($fp);
    return $tmpfile;
}

#
# This function quotes arguments for shell command according
# to the host operation system
#
function func_shellquote() {
	static $win_s = array(' ', '&');
	static $win_r = array('" "','"&"');
	$result = "";
	$args = func_get_args();
	foreach ($args as $idx=>$arg)
		$args[$idx] = X_DEF_OS_WINDOWS ? (str_replace($win_s,$win_r,$arg)) : (escapeshellarg($arg));

	return implode(' ', $args);
}

?>

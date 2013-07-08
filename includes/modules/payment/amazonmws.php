<?php
// +--------------------------------------------------------------------+
// | bitcommerce														|
// +--------------------------------------------------------------------+
// | Copyright (c) 2011 bitcommerce.org									|
// |																	|
// | http://www.bitcommerce.org											|
// +--------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license		|
// +--------------------------------------------------------------------+

require_once( BITCOMMERCE_PKG_PATH.'classes/CommercePluginPaymentBase.php' );

class amazonmws extends CommercePluginPaymentBase { 
	var $code;
	var $title;
	var $description;
	var $enabled; 

	// class constructor
	function __construct( $pAmazonOrdersId=NULL ) {
		$this->mAmazonOrdersId = $pAmazonOrdersId;
		$this->code = 'amazonmws';
		parent::__construct();
		if ( !empty( $_GET['main_page'] ) ) {
			$this->title = ''; // Payment Module title in Catalog
		} else {
			$this->title = tra( 'AmazonMWS' ); // Payment Module title in Admin
		}
		$this->description = tra( 'AmazonMWS Order Integration' );
		$this->sort_order = 998;
		$this->enabled = ((defined( 'MODULE_PAYMENT_AMAZONMWS_STATUS' ) && MODULE_PAYMENT_AMAZONMWS_STATUS == 'True') ? true : false);
		$this->credit_class = true;
	}

	function check() {
		global $gBitDb;
		if( !isset( $this->_check ) ) {
			$this->_check = 'True' == $gBitDb->getOne("select `configuration_value` from " . TABLE_CONFIGURATION . " where `configuration_key` = ?", array( strtoupper( 'MODULE_PAYMENT_'.$this->code.'_STATUS' ) ) );
		}
		return $this->_check;
	}

	function process() {
		global $order, $currencies, $gBitDb;
		$amazonFee = $this->getAmazonSellerFee( $order->info['total'] );
		$order->info['total'] -= $amazonFee; 
		$ret = array( 'code' => 'pay_amazonmws',
					 'title' => tra( 'Amazon Order' ) . ': ' . $this->mAmazonOrdersId,
					 'text' => '-' . $currencies->format( $amazonFee ),
					 'value' => -1 * $amazonFee,
						'sort_order' => $this->sort_order );
		return $ret;
	}

	function selection() {
		return "";
	}

	function javascript_validation() {
		return "";
	}

	function getAmazonSellerFee( $pFee ) {
		$feePercentage = 0;
		$feeClosing = 0;
/*
		swtich ( _need_to_get_product_category_ ) {
			'Amazon Kindle':
				$feePercentage = .15;	
			'Baby Products':
				$feePercentage = .15;	
				break;
			'Beauty':
				$feePercentage = .15;	
				break;
			'Books':
				$feePercentage = .15;
				$feeClosing = 1.35;
				break;
			'Camera & Photo':
				$feePercentage = .8;
				break;
			'Cell Phones & Accessories':
				$feePercentage = .15;	 
				break;
			'Consumer Electronics':
				$feePercentage = .8;
				break;
			'DVDs & Videos VHS':
				$feePercentage = .15;
				$feeClosing = 0.80;
				break;
			'Grocery & Gourmet Food':
				$feePercentage = .20;
				break;
			'Health & PersonalCare':
				$feePercentage = .15;
				break;
			'Home & Garden (including Pet Supplies) 2':
				$feePercentage = .15;
				break;
			'Kitchen':
				$feePercentage = .15;
				break;
			'Music':
				$feePercentage = .15;
				$feeClosing = 0.80;
				break;
			'Musical Instruments':
				$feePercentage = .12;
				break;
			'Office Products':
				$feePercentage = .15;
				break;
			'Personal Computer':
				$feePercentage = .6;
				break;
			'Software':
				$feePercentage = .15;
				$feeClosing = 1.35;
				break;
			'Sports & Outdoors':
				$feePercentage = .15;
				break;
			'Tools & Home Improvement':
				$feePercentage = .12;
				break;
			'Toys & Games':
				$feePercentage = .15;
				break;
			'Video Games':
				$feePercentage = .15;
				$feeClosing = 1.35;
				break;
			'Video Game Consoles':
				$feePercentage = .8;
				$feeClosing = 1.35;
				break;
		}
*/


		// Fixed to book right now be cause no other way to figure it out currently
		$feePercentage = .15;
		$feeClosing = 1.35;

		return ($pFee * $feePercentage) + $feeClosing;
	}

	
	function install() {
		global $gBitDb, $gBitUser;
		$gBitDb->StartTrans();
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Enable AmazonMWS Module', 'MODULE_PAYMENT_AMAZONMWS_STATUS', 'True', 'Do you want enable AmazonMWS integration?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Local Username', 'MODULE_PAYMENT_AMAZONMWS_LOCAL_USERNAME','amazonmws', 'This is the username on this site under which all orders will be processed.', '6', '4', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Merchant ID', 'MODULE_PAYMENT_AMAZONMWS_MERCHANT_ID','', '', '6', '4', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Marketplace ID', 'MODULE_PAYMENT_AMAZONMWS_MARKETPLACE_ID','', '', '6', '4', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('AWS Access Key ID', 'MODULE_PAYMENT_AMAZONMWS_AWS_ACCESS_KEY_ID','', '', '6', '4', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Secret Key', 'MODULE_PAYMENT_AMAZONMWS_SECRET_KEY','', '', '6', '4', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `use_function`, `date_added`) values ('Initial Order Status', 'MODULE_PAYMENT_AMAZONMWS_INITIAL_ORDER_STATUS_ID', '20', 'Orders with this status will be processed for fulfillment<br />(\'Transferred\' recommended)', '6', '5', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Default Attributes', 'MODULE_PAYMENT_AMAZONMWS_DEFAULT_ATTRIBUTES','', 'Comma separated list of <a href=\"products_options.php\">product options ids</a> that will be used if amazon SKU has none.', '6', '4', now())");

		if( !$gBitUser->lookupHomepage( 'amazonmws' ) ) {
			$newUser = new BitPermUser();
			$userHash['login'] = 'amazonmws';
			$userHash['email'] = str_replace( '@', '+amazonmws@', STORE_OWNER_EMAIL_ADDRESS );
			$userHash['real_name'] = 'Amazon Marketplace';
			$userHash['hash'] = $gBitUser->getField( 'hash' );
			$newUser->importUser( $userHash );
		}
		$gBitDb->CompleteTrans();
	}

	 function remove() {
		 global $gBitDb;
		 $gBitDb->Execute("delete from " . TABLE_CONFIGURATION . " where `configuration_key` LIKE	'MODULE_PAYMENT_AMAZONMWS%'");
	 }

	 function keys() {
		 return array(
			'MODULE_PAYMENT_AMAZONMWS_STATUS',
			'MODULE_PAYMENT_AMAZONMWS_LOCAL_USERNAME',
			'MODULE_PAYMENT_AMAZONMWS_MERCHANT_ID',
			'MODULE_PAYMENT_AMAZONMWS_MARKETPLACE_ID',
			'MODULE_PAYMENT_AMAZONMWS_AWS_ACCESS_KEY_ID',		
			'MODULE_PAYMENT_AMAZONMWS_SECRET_KEY',		
			'MODULE_PAYMENT_AMAZONMWS_INITIAL_ORDER_STATUS_ID',
			'MODULE_PAYMENT_AMAZONMWS_DEFAULT_ATTRIBUTES',
		 );
	 }

 }
?>

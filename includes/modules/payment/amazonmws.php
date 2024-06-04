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

require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommercePluginPaymentCardBase.php' );

class amazonmws extends CommercePluginPaymentBase { 
	var $code;
	var $title;
	var $description;
	var $enabled; 

	// class constructor
	function __construct( $pAmazonOrdersId=NULL ) {
		parent::__construct();
		$this->mAmazonOrdersId = $pAmazonOrdersId;
		$this->title = 'Pay with Amazon'; // Payment Module title in Catalog
		$this->adminTitle = tra( 'AmazonMWS' ); // Payment Module title in Admin
		$this->description = tra( 'AmazonMWS Order Integration' );
	}

	function process( $pPaymentParams = array() ) {
		global $order, $currencies, $gBitDb;
		$amazonFee = $this->getAmazonSellerFee( $order->info['total'] );
		$order->info['total'] -= $amazonFee; 
		$ret = array(	'code' => 'pay_amazonmws',
						'title' => tra( 'Amazon Order' ) . ': ' . $this->mAmazonOrdersId,
						'text' => '-' . $currencies->format( $amazonFee ),
						'value' => -1 * $amazonFee,
						'sort_order' => $this->sort_order );
		return $ret;
	}

	protected function getSessionVars() {
		return array( 'amazonmws' );
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
		parent::install();
		global $gBitDb, $gBitUser;
		$gBitDb->StartTrans();
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`) values ('Local Username', 'MODULE_PAYMENT_AMAZONMWS_LOCAL_USERNAME','amazonmws', 'This is the username on this site under which all orders will be processed.', '6', '4')");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`) values ('Merchant ID', 'MODULE_PAYMENT_AMAZONMWS_MERCHANT_ID','', '', '6', '4')");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`) values ('Marketplace ID', 'MODULE_PAYMENT_AMAZONMWS_MARKETPLACE_ID','', '', '6', '4')");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`) values ('AWS Access Key ID', 'MODULE_PAYMENT_AMAZONMWS_AWS_ACCESS_KEY_ID','', '', '6', '4')");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`) values ('Secret Key', 'MODULE_PAYMENT_AMAZONMWS_SECRET_KEY','', '', '6', '4')");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`) values ('Default Attributes', 'MODULE_PAYMENT_AMAZONMWS_DEFAULT_ATTRIBUTES','', 'Comma separated list of <a href=\"products_options.php\">product options ids</a> that will be used if amazon SKU has none.', '6', '4')");

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

	public function keys() {
		return array_merge( 
					array_keys( $this->config() ), 
					array(
						'MODULE_PAYMENT_AMAZONMWS_LOCAL_USERNAME',
						'MODULE_PAYMENT_AMAZONMWS_MERCHANT_ID',
						'MODULE_PAYMENT_AMAZONMWS_MARKETPLACE_ID',
						'MODULE_PAYMENT_AMAZONMWS_AWS_ACCESS_KEY_ID',		
						'MODULE_PAYMENT_AMAZONMWS_SECRET_KEY',		
						'MODULE_PAYMENT_AMAZONMWS_DEFAULT_ATTRIBUTES',
					 )
				);
	 }

 }
?>

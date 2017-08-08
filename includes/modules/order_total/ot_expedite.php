<?php
// +----------------------------------------------------------------------+
// | bitcommerce,	http://www.bitcommerce.org                            |
// +----------------------------------------------------------------------+
// | Copyright (c) 2017 bitcommerce.org                                   |
// | This source file is subject to version 3.0 of the GPL license        |
// | Portions Copyrigth (c) 2005 http://www.zen-cart.com                  |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+

require_once( BITCOMMERCE_PKG_PATH.'classes/CommercePluginOrderTotalBase.php' );

class ot_expedite extends CommercePluginOrderTotalBase {

	private $mExpediteFlag = FALSE;

	function __construct( $pOrder=NULL ) {
		parent::__construct( $pOrder );
		$this->code = 'ot_expedite';

		$this->title = tra( 'Expedited Processing' );
		$this->description = '';
		if( $this->isEnabled() ) {
			$this->sort_order = MODULE_ORDER_TOTAL_EXPEDITE_SORT_ORDER;
			if( !empty( $_SESSION['has_expedite'] ) ) {
				$this->mExpediteFlag = TRUE;
			}
		}

		$this->mExpediteFlag = $this->getParameter( $_SESSION, 'ot_expedite' );

		if( isset( $_REQUEST['ot_expedite'] ) ) {
			$this->setExpedite( $_REQUEST['ot_expedite'] == 1 );
			bit_redirect( $_SERVER['HTTP_REFERER'] );
		}
	}

	protected function getStatusKey() {
		return 'MODULE_ORDER_TOTAL_EXPEDITE_STATUS';
	}

	protected function hasExpedite() {
		return $this->mExpediteFlag == TRUE;
	}

	protected function setExpedite( $pExpedite ) {
		$this->hasExpedite = $pExpedite;
		$_SESSION['ot_expedite'] = $pExpedite;
	}

	public function getSortOrder() {
		if( $this->hasExpedite() ) {
			$ret = $this->sort_order;
		} else {
			$ret = 9999; // last on the list with a button to enable
		}

		return $ret;
	}

	function process() {
		global $gCommerceSystem, $currencies;
		parent::process();

		if( $this->isEnabled() ) {
			if( $this->mOrder->isExpeditable() ) {
				if( strpos( MODULE_ORDER_TOTAL_EXPEDITE_ORDER_FEE, '%' ) ) {
					$expediteMultiplier = (float)MODULE_ORDER_TOTAL_EXPEDITE_ORDER_FEE / 100;
					$exepditeDisplay = MODULE_ORDER_TOTAL_EXPEDITE_ORDER_FEE;
				} elseif( is_numeric( MODULE_ORDER_TOTAL_EXPEDITE_ORDER_FEE ) ) {
					$expediteMultiplier = MODULE_ORDER_TOTAL_EXPEDITE_ORDER_FEE;
					$exepditeDisplay = $currencies->format( MODULE_ORDER_TOTAL_EXPEDITE_ORDER_FEE, true,   $this->mOrder->info['currency'], $this->mOrder->info['currency_value'] );
				}

				$expediteTitle = $this->title . ' ( '.$exepditeDisplay. ' )';
				if( $gCommerceSystem->getConfig( 'MODULE_ORDER_TOTAL_EXPEDITE_INFO_URL' )  ) {
					$expediteTitle .= '<div class="small"><a href="'.MODULE_ORDER_TOTAL_EXPEDITE_INFO_URL.'">'.'Terms &amp; Conditions'.'</a></div>';
				}
				$expediteCost = $this->mOrder->info['total'] * $expediteMultiplier;
				$expediteFormatted = $currencies->format($expediteCost, true,	$this->mOrder->info['currency'], $this->mOrder->info['currency_value']);
				if( $this->hasExpedite() ) {
					$this->mOrder->info['total'] += $expediteCost;
					$expediteText = ' <a class="" href="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&amp;ot_expedite=0"><i class="icon-remove-sign"></i></a> '.$expediteFormatted;
				} else {
					$expediteText = '<a class="btn btn-default btn-xs" href="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&amp;ot_expedite=1"><i class="icon-plus-sign"></i> '.$expediteFormatted.'</a>';
				}
			} else {
				$expediteTitle = "Expedited order processing is not available for 1 or more items in your cart.";
				$expediteText = '';
				$expediteCost = 0;	
			}

			$this->mProcessingOutput = array( 'code' => $this->code,
												'sort_order' => $this->getSortOrder(),
												'title' => $expediteTitle,
												'text' => $expediteText,
												'value' => $expediteCost);
		}
	}

	function check() {
		global $gBitDb;
		if (!isset($this->_check)) {
			$check_query = $gBitDb->Execute("select `configuration_value` from " . TABLE_CONFIGURATION . " where `configuration_key` = 'MODULE_ORDER_TOTAL_EXPEDITE_STATUS'");
			$this->_check = $check_query->RecordCount();
		}

		return $this->_check;
	}
//lagt tilk servicepakke her!!!!
	function keys() {
		return array('MODULE_ORDER_TOTAL_EXPEDITE_STATUS', 'MODULE_ORDER_TOTAL_EXPEDITE_SORT_ORDER', 'MODULE_ORDER_TOTAL_EXPEDITE_ORDER_FEE', 'MODULE_ORDER_TOTAL_EXPEDITE_INFO_URL');
	}

	function install() {
		global $gBitDb;
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Display Expedite', 'MODULE_ORDER_TOTAL_EXPEDITE_STATUS', 'true', 'Do you want this module to display?', '6', '1','zen_cfg_select_option(array(''true'', ''false''), ', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Sort Order', 'MODULE_ORDER_TOTAL_EXPEDITE_SORT_ORDER', '950', 'Sort order of display.', '6', '2', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Expedite Order Fee', 'MODULE_ORDER_TOTAL_EXPEDITE_ORDER_FEE', '25%', 'Expedite Fee. Examples: 15%, or 10.00 (native currency will be used)', '6', '3', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Expedite Information URL', 'MODULE_ORDER_TOTAL_EXPEDITE_INFO_URL', NULL, 'URL with information on Expedite Service', '6', '3', now())");
	}


	function remove() {
		global $gBitDb;
		$keys = '';
		$keys_array = $this->keys();
		$keys_size = sizeof($keys_array);
		for ($i=0; $i<$keys_size; $i++) {
			$keys .= "'" . $keys_array[$i] . "',";
		}
		$keys = substr($keys, 0, -1);

		$gBitDb->Execute("delete from " . TABLE_CONFIGURATION . " where `configuration_key` in (" . $keys . ")");
	}
}

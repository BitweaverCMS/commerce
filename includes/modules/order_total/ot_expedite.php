<?php
// +----------------------------------------------------------------------+
// | bitcommerce,	http://www.bitcommerce.org                            |
// +----------------------------------------------------------------------+
// | Copyright (c) 2017 bitcommerce.org                                   |
// | This source file is subject to version 3.0 of the GPL license        |
// | Portions Copyrigth (c) 2005 http://www.zen-cart.com                  |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+

require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommercePluginOrderTotalBase.php' );

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

		$this->setExpedite( FALSE );
		if( isset( $_REQUEST['ot_expedite'] ) ) {
			$this->setExpedite( $_REQUEST['ot_expedite'] == 1 );
			bit_redirect( $_SERVER['HTTP_REFERER'] );
		}
	}

	protected function hasExpedite() {
		return $this->mExpediteFlag == TRUE;
	}

	protected function setExpedite( $pExpedite ) {
		$this->mExpediteFlag = $pExpedite;
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

	function process( $pSessionParams = array() ) {
		parent::process( $pSessionParams );
		global $gCommerceSystem, $currencies;

		if( $this->isEnabled() ) {
			if( $this->mOrder->isExpeditable() ) {
				if( !empty( $pSessionParams['ot_expedite'] ) ) {
					$this->setExpedite( $pSessionParams['ot_expedite'] == (int)1 );
				}
				if( strpos( MODULE_ORDER_TOTAL_EXPEDITE_ORDER_FEE, '%' ) ) {
					$expediteMultiplier = (float)MODULE_ORDER_TOTAL_EXPEDITE_ORDER_FEE / 100;
					$exepditeDisplay = MODULE_ORDER_TOTAL_EXPEDITE_ORDER_FEE;
				} elseif( is_numeric( MODULE_ORDER_TOTAL_EXPEDITE_ORDER_FEE ) ) {
					$expediteMultiplier = MODULE_ORDER_TOTAL_EXPEDITE_ORDER_FEE;
					$exepditeDisplay = $currencies->format( MODULE_ORDER_TOTAL_EXPEDITE_ORDER_FEE, true,   $this->mOrder->info['currency'], $this->mOrder->info['currency_value'] );
				}

				$expediteTitle = '';
				if( !$this->hasExpedite() ) {
					$expediteTitle = "Add ";
				}
				$expediteTitle .= $this->title . ' ( '.$exepditeDisplay. ' )';
				if( $gCommerceSystem->getConfig( 'MODULE_ORDER_TOTAL_EXPEDITE_INFO_URL' )  ) {
					$expediteTitle .= '<div class="small"><a href="'.MODULE_ORDER_TOTAL_EXPEDITE_INFO_URL.'">'.'Terms &amp; Conditions'.'</a></div>';
				}
				$expediteCost = $this->mOrder->info['total'] * $expediteMultiplier;
				$expediteFormatted = $currencies->format($expediteCost, true,	$this->mOrder->info['currency'], $this->mOrder->info['currency_value']);
				if( $this->hasExpedite() ) {
					$this->mOrder->info['total'] += $expediteCost;
					$expediteText = ' <a class="btn btn-primary btn-xs" href="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&amp;ot_expedite=0"><i class="fa fal fa-circle-minus"></i> '.$expediteFormatted.'</a>';
				} else {
					$expediteText = '<a class="btn btn-primary btn-xs" href="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&amp;ot_expedite=1"><i class="fa fal fa-circle-plus"></i> '.$expediteFormatted.'</a>';
				}
			} else {
				$expediteTitle = "Expedited order processing is not available for 1 or more items in your cart.";
				$expediteText = '';
				$expediteCost = 0;	
			}

			if( BitBase::getParameter( $_REQUEST, 'main_page' ) != 'checkout_process' || $this->hasExpedite() ) {
				$this->mProcessingOutput = array( 'code' => $this->code,
													'sort_order' => $this->getSortOrder(),
													'title' => $expediteTitle,
													'text' => $expediteText,
													'value' => $expediteCost);
			}

			if( BitBase::getParameter( $_REQUEST, 'main_page' ) == 'checkout_process' && $this->hasExpedite() ) {
				$this->mOrder->info['comments'] = trim( "EXPEDITE ORDER\n\n".$this->mOrder->info['comments'] );
			}
		}
	}

	public function keys() {
		return array_merge(
					array_keys( $this->config() ),
					array('MODULE_ORDER_TOTAL_EXPEDITE_ORDER_FEE', 'MODULE_ORDER_TOTAL_EXPEDITE_INFO_URL')
				);
	}

	function install() {
		parent::install();
		$this->mDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Expedite Order Fee', 'MODULE_ORDER_TOTAL_EXPEDITE_ORDER_FEE', '25%', 'Expedite Fee. Examples: 15%, or 10.00 (native currency will be used)', '6', '3', now())");
		$this->mDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Expedite Information URL', 'MODULE_ORDER_TOTAL_EXPEDITE_INFO_URL', NULL, 'URL with information on Expedite Service', '6', '3', now())");
	}

	/*
	* rows for com_configuration table as associative array of column => value
	*/
	protected function config() {
		$ret = parent::config();
		// set some default values
		$ret[$this->getModuleKeyTrunk().'_SORT_ORDER']['configuration_value'] = '950';
		return $ret;
	}
}

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

		$this->title = $this->getTitle( 'Expedited Processing' );
		$this->description = '';
		if( $this->isEnabled() ) {
			$this->sort_order = MODULE_ORDER_TOTAL_EXPEDITE_SORT_ORDER;
			if( !empty( $_SESSION['has_expedite'] ) ) {
				$this->mExpediteFlag = TRUE;
			}
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

	function process( $pPaymentParams, &$pSessionParams ) {
		parent::process( $pPaymentParams, $pSessionParams );
		global $gCommerceSystem, $currencies;

		if( $this->isEnabled() ) {
			if( $this->mOrder->isExpeditable() ) {
				if( isset( $pPaymentParams['ot_expedite'] ) ) {
					$this->setExpedite( (int)$pPaymentParams['ot_expedite'] == 1 );
				} elseif( isset( $pSessionParams['ot_expedite'] ) ) {
					$this->setExpedite( (int)$pSessionParams['ot_expedite'] == 1 );
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

				$queryHash = array(); parse_str( $_SERVER['QUERY_STRING'], $queryHash );
				$queryHash['ot_expedite'] = NULL; // unset ot_expedite of current query, so toggle can be set in Expedited Text
				$queryString = http_build_query( $queryHash, '', '&amp;' );

				if( $this->hasExpedite() ) {
					$this->mOrder->info['total'] += $expediteCost;
					$expediteText = ' <a class="btn btn-primary btn-xs" href="'.$_SERVER['PHP_SELF'].'?'.$queryString.'&amp;ot_expedite=0"><i class="fa fal fa-circle-minus"></i> '.$expediteFormatted.'</a>';
				} else {
					$expediteText = '<a class="btn btn-primary btn-xs" href="'.$_SERVER['PHP_SELF'].'?'.$queryString.'&amp;ot_expedite=1"><i class="fa fal fa-circle-plus"></i> '.$expediteFormatted.'</a>';
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

	protected function config() {
		$i = 20;
		return array_merge( parent::config(), array( 
			$this->getModuleKeyTrunk().'_ORDER_FEE' => array(
				'configuration_title' => 'Expedite Order Fee',
				'configuration_description' => 'Expedite Fee. Examples: 15%, or 10.00 (native currency will be used)',
				'configuration_value' => '25%',
				'sort_order' => $i++,
			),
			$this->getModuleKeyTrunk().'_INFO_URL' => array(
				'configuration_title' => 'Expedite Information URL',
				'configuration_description' => 'URL with information on Expedite Service',
				'sort_order' => $i++,
			)
		) );
	}
}

<?php
/**
 * @package bitcommerce
 * @author spiderr <spiderr@bitweaver.org>
 *
 * Copyright (c) 2013 bitweaver.org
 * All Rights Reserved. See below for details and a complete list of authors.
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE. See http://www.gnu.org/licenses/gpl.html for details
 */

require_once( BITCOMMERCE_PKG_PATH.'classes/CommercePluginBase.php' );

abstract class CommercePluginShippingBase extends CommercePluginBase {

	protected $mShipZones = NULL;

	abstract public function quote( $pShipHash );

	public function __construct() {
		parent::__construct();
		$this->quotes = array();
		$this->icon = 'shipping_'.$this->code;
		$this->title = $this->getConfig( 'MODULE_SHIPPING_'.$this->mConfigKey.'_TEXT_TITLE', $this->code );
		$this->description = $this->getConfig( 'MODULE_SHIPPING_'.$this->mConfigKey.'_TEXT_DESCRIPTION' );
		$this->sort_order = $this->getConfig( 'MODULE_SHIPPING_'.$this->mConfigKey.'_SORT_ORDER' );
		$this->tax_class = $this->getConfig( 'MODULE_SHIPPING_'.$this->mConfigKey.'_TAX_CLASS' );
		$this->tax_basis = $this->getConfig( 'MODULE_SHIPPING_'.$this->mConfigKey.'_TAX_BASIS' );
	}

	protected function getStatusKey() {
		return 'MODULE_SHIPPING_'.$this->mConfigKey.'_STATUS';
	}

	protected function getShipperZone() {
		return $this->getConfig( 'MODULE_SHIPPING_'.$this->mConfigKey.'_ZONE' );
	}

	protected function getShippingTax() {
		$ret = 0;
		
		if( !empty( $this->tax_class ) ) {
			$ret = zen_get_tax_rate($this->tax_class, $pShipHash['destination']['countries_id'], $pShipHash['destination']['zone_id']);
		}

		return $ret;
	}

	public function maxShippingWeight() {
		return (float)$this->getConfig( 'SHIPPING_MAX_WEIGHT' );
	}

	protected function isInternationOrder( $pShipHash ) {
		return $pShipHash['origin']['countries_id'] != $pShipHash['destination']['countries_id'];	
	}

	protected function isEligibleShipper( $pShipHash ) {

		$freeShipping = FALSE;
/*
    $total_count = $total_count - $gBitCustomer->mCart->in_cart_check('product_is_free','1');
    $total_count = $total_count - $gBitCustomer->mCart->in_cart_check('product_is_always_free_ship','1');
		$total_count = $total_count - $gBitCustomer->mCart->free_shipping_items();

*/

/*
		if( $this->isEnabled() ) {
			if(defined("SHIPPING_ORIGIN_COUNTRY")) {
				if ((int)SHIPPING_ORIGIN_COUNTRY > 0) {
					$countries_array = zen_get_countries(SHIPPING_ORIGIN_COUNTRY);
					$this->country = $countries_array['countries_iso_code_2'];
				} else {
					$this->country = SHIPPING_ORIGIN_COUNTRY;
				}
			} else {
				$this->country = STORE_ORIGIN_COUNTRY;
			}
		}
*/
		$quoteBase = array();

		if( $this->enabled && !empty( $pShipHash['shipping_weight_total'] ) ) {
			$pass = TRUE;
			// Check to see if shipping module is zone silo'ed
			if( ($shipperZone = $this->getShipperZone()) && !$freeShipping && $ret = !empty( $pShipHash['destination'] ) && !empty( $pShipHash['origin'] ) ) {
				if( is_null( $this->mShipZones ) ) {
					// cache mShipZones in memory
					$this->mShipZones = $this->mDb->getCol( "SELECT `zone_id` FROM " . TABLE_ZONES_TO_GEO_ZONES . " WHERE `geo_zone_id` = ? ORDER BY `zone_id`", array( $shipperZone ), FALSE, BIT_QUERY_CACHE_TIME );
				}

				if( count( $this->mShipZones ) ) {
					$pass = FALSE;
					foreach( $this->mShipZones as $zoneId ) {
						if(  $pShipHash['destination']['countries_id'] && $zoneId == $pShipHash['destination']['zone_id']) {
							$pass = TRUE;
							break;
						}
					}
				} else {
					$pass = TRUE;
				}
			}

			// if ($error == true) $quotes['error'] = MODULE_SHIPPING_ZONES_INVALID_ZONE;

			if( $pass ) {
				switch (SHIPPING_BOX_WEIGHT_DISPLAY) {
					case (0):
						$show_box_weight = '';
						break;
					case (1):
						$show_box_weight = '(' . $pShipHash['shipping_num_boxes']. ' ' . TEXT_SHIPPING_BOXES . ')';
						break;
					case (2):
						$show_box_weight = '(' . number_format($pShipHash['shipping_weight_total'] * $pShipHash['shipping_num_boxes'],2) . tra( 'lbs' ) . ')';
						break;
					default:
						$show_box_weight = '(' . $pShipHash['shipping_num_boxes'] . ' x ' . number_format($pShipHash['shipping_weight_total'],2) . tra( 'lbs' ) . ')';
						break;
				}
				$quoteBase = array(
									'id' => $this->code,
									'module' => $this->title,
									'weight' => $show_box_weight,
									'icon' => $this->icon,
									'tax' => $this->getShippingTax( $pShipHash )
								);
			}
		}

		return $quoteBase;
	}

	/**
	 * Install this module
	 *
	 */
	function install() {
		if( !$this->isInstalled() ) {
			$this->mDb->StartTrans();
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Enable ".$this->title."', 'MODULE_SHIPPING_".$this->getConfigKey()."_STATUS', 'True', 'Do you want to offer ".$this->title."?', '6', '0', 'zen_cfg_select_option(array(''True'', ''False''), ', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Sort Order', 'MODULE_SHIPPING_".$this->getConfigKey()."_SORT_ORDER', '0', 'Sort order of display.', '6', '0', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `use_function`, `set_function`, `date_added`) values ('Shipping Zone', 'MODULE_SHIPPING_".$this->getConfigKey()."_ZONE', '0', 'If a zone is selected, only enable this shipping method for that zone.', '6', '0', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `use_function`, `set_function`, `date_added`) values ('Tax Class', 'MODULE_SHIPPING_".$this->getConfigKey()."_TAX_CLASS', '0', 'Use the following tax class on the shipping fee.', '6', '0', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Tax Basis', 'MODULE_SHIPPING_".$this->getConfigKey()."_TAX_BASIS', 'Shipping', 'On what basis is Shipping Tax calculated. Options are<br />Shipping - Based on customers Shipping Address<br />Billing Based on customers Billing address<br />Store - Based on Store address if Billing/Shipping Zone equals Store zone', '6', '0', 'zen_cfg_select_option(array(''Shipping'', ''Billing'', ''Store''), ', now())");
			$this->mDb->CompleteTrans();
		}
	}
	/**
	 * Build array of keys used for installing/managing this module
	 *
	 * @return array
	 */
	function keys() {
		return array( 
					'MODULE_SHIPPING_'.$this->getConfigKey().'_STATUS',
					'MODULE_SHIPPING_'.$this->getConfigKey().'_SORT_ORDER',
					'MODULE_SHIPPING_'.$this->getConfigKey().'_TAX_CLASS',
					'MODULE_SHIPPING_'.$this->getConfigKey().'_TAX_BASIS',
					'MODULE_SHIPPING_'.$this->getConfigKey().'_ZONE',
					);
	}
}

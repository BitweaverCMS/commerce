<?php
// +----------------------------------------------------------------------+
// | bitcommerce,	http://www.bitcommerce.org                            |
// +----------------------------------------------------------------------+
// | Copyright (c) 2019 bitcommerce.org                                   |
// | This source file is subject to version 3.0 of the GPL license        |
// | Portions Copyrigth (c) 2005 http://www.zen-cart.com                  |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+

require_once( BITCOMMERCE_PKG_PATH.'classes/CommercePluginShippingBase.php' );

class supersaver extends CommercePluginShippingBase {

	function __construct() {
		parent::__construct();
		$this->title = tra( 'SuperSaver Shipping' );
		$this->description = tra( 'Offer fixed rate (or free!) shipping for orders within a specified amount.' );
	}

	function quote( $pShipHash ) {
		if( $quotes = $this->isEligibleShipper( $pShipHash ) ) {

			$min = $this->getConfig( 'MODULE_SHIPPING_SUPERSAVER_MIN' );
			$max = $this->getConfig( 'MODULE_SHIPPING_SUPERSAVER_MAX' );

			$shippedTotal = $pShipHash['shipping_value'];

			if( !empty( $min ) && $shippedTotal < MODULE_SHIPPING_SUPERSAVER_MIN ) {
				$quotes['error'] = tra( 'You must spend at least '. $currencies->format( MODULE_SHIPPING_SUPERSAVER_MIN ).' to get SuperSaver Shipping.' ). ' <a href="'.zen_href_link(FILENAME_SHOPPING_CART).'">'.tra( 'Update Cart' ).'</a>';
			} elseif( !empty( $max ) && $shippedTotal > MODULE_SHIPPING_SUPERSAVER_MAX ) {
				// no quote for you!
				$quotes['error'] = tra( 'SuperSaver Shipping only applies to orders up to '.$currencies->format( MODULE_SHIPPING_SUPERSAVER_MAX ) ). ' <a href="'.zen_href_link(FILENAME_SHOPPING_CART).'">'.tra( 'Update Cart' ).'</a>';
			} else {
				if( $this->isInternationOrder( $pShipHash ) ) {
					if( $this->isEnabled( 'MODULE_SHIPPING_SUPERSAVER_INTL' ) ) {
						$desc = tra( MODULE_SHIPPING_SUPERSAVER_DESC ).' '.tra( MODULE_SHIPPING_SUPERSAVER_INTL_DESC );
						$quotes['methods'][] = array(
													'id' => $this->code,
													'title' => trim( $desc ),
													'code' => 'supersaverintl',
													'transit_time' => MODULE_SHIPPING_SUPERSAVER_INTL_TRANSIT_TIME,
													'cost' => MODULE_SHIPPING_SUPERSAVER_INTL_COST + MODULE_SHIPPING_SUPERSAVER_HANDLING
												);
					}
				} elseif( $this->isEnabled( 'MODULE_SHIPPING_SUPERSAVER_DOMESTIC' ) ) {
					$desc = tra( MODULE_SHIPPING_SUPERSAVER_DESC ).' '.tra( MODULE_SHIPPING_SUPERSAVER_DOMESTIC_DESC );
					$quotes['methods'][] = array(
												'id' => $this->code,
												'title' => trim( $desc ),
												'code' => 'supersaver',
												'transit_time' => MODULE_SHIPPING_SUPERSAVER_DOMESTIC_TRANSIT_TIME,
												'cost' => MODULE_SHIPPING_SUPERSAVER_DOMESTIC_COST + MODULE_SHIPPING_SUPERSAVER_HANDLING
												);
				}
			}
		}

		return $quotes;
	}

	function install() {
		if( !$this->isInstalled() ) {
			parent::install();
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Minimum Cart Value', 'MODULE_SHIPPING_SUPERSAVER_MIN', '30.00', 'What is the minimum cart total to get supersaver shipping?', '7', '6', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Maximum Cart Value', 'MODULE_SHIPPING_SUPERSAVER_MAX', '', 'What is the maximum cart total to get supersaver shipping?', '7', '6', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('SuperSaver Shipping Cost', 'MODULE_SHIPPING_SUPERSAVER_DOMESTIC_COST', '4.99', 'What is the SuperSaver Shipping cost?', '7', '6', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Handling Fee', 'MODULE_SHIPPING_SUPERSAVER_HANDLING', '0', 'Handling fee for this shipping method.', '7', '0', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('SuperSaver Shipping Description', 'MODULE_SHIPPING_SUPERSAVER_DESC', 'SuperSaver', 'Text to accompany all SuperSaver quotes', '7', '6', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Domestic SuperSaver Shipping', 'MODULE_SHIPPING_SUPERSAVER_DOMESTIC', 'True', 'Allow domestic SuperSaver shipping - the same country as the <a href=\"configuration.php?gID=5&cID=123&action=edit\">Default Country</a>.', '7', '0', 'zen_cfg_select_option(array(''True'', ''False''), ', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Domestic SuperSaver Shipping Description', 'MODULE_SHIPPING_SUPERSAVER_DOMESTIC_DESC', 'Domestic', 'Text to accompany SuperSaver domestic quote', '7', '6', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Domestic SuperSaver Shipping Transit Time', 'MODULE_SHIPPING_SUPERSAVER_DOMESTIC_TRANSIT_TIME', '1-2 weeks', 'Transit time to accompany SuperSaver domestic quote', '7', '6', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('International SuperSaver Shipping', 'MODULE_SHIPPING_SUPERSAVER_INTL', 'True', 'Allow international SuperSaver shipping - countries outside of the <a href=\"configuration.php?gID=5&cID=123&action=edit\">Default Country</a>.', '7', '0', 'zen_cfg_select_option(array(''True'', ''False''), ', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('International SuperSaver Shipping Description', 'MODULE_SHIPPING_SUPERSAVER_INTL_DESC', 'International', 'Text to accompany SuperSaver international quote', '7', '6', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('International SuperSaver Shipping Transit Time', 'MODULE_SHIPPING_SUPERSAVER_INTL_TRANSIT_TIME', '4-8 weeks', 'Transit time to accompany SuperSaver international quote', '7', '6', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('SuperSaver Shipping Cost', 'MODULE_SHIPPING_SUPERSAVER_INTL_COST', '14.99', 'What is the SuperSaver Shipping International cost?', '7', '6', now())");
		}
	}

	function keys() {
		return array_merge( parent::keys(), array(
			'MODULE_SHIPPING_SUPERSAVER_HANDLING',
			'MODULE_SHIPPING_SUPERSAVER_MIN',
			'MODULE_SHIPPING_SUPERSAVER_MAX',
			'MODULE_SHIPPING_SUPERSAVER_DESC',
			'MODULE_SHIPPING_SUPERSAVER_DOMESTIC_TRANSIT_TIME',
			'MODULE_SHIPPING_SUPERSAVER_DOMESTIC',
			'MODULE_SHIPPING_SUPERSAVER_DOMESTIC_COST',
			'MODULE_SHIPPING_SUPERSAVER_DOMESTIC_DESC',
			'MODULE_SHIPPING_SUPERSAVER_INTL',
			'MODULE_SHIPPING_SUPERSAVER_INTL_COST',
			'MODULE_SHIPPING_SUPERSAVER_INTL_TRANSIT_TIME',
			'MODULE_SHIPPING_SUPERSAVER_INTL_DESC',
		) );
	}
}


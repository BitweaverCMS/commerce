<?php
// +----------------------------------------------------------------------+
// | bitcommerce															|
// | Copyright (c) 2007-2009 bitcommerce.org									 |
// | http://www.bitcommerce.org											 |
// | This source file is subject to version 2.0 of the GPL license		|
// +----------------------------------------------------------------------+
/**
 * @version	$Header$
 *
 * Base class for handling common functionality between shipping cart and orders
 *
 * @package	bitcommerce
 * @author	 spider <spider@steelsun.com>
 */


class CommerceOrderBase extends BitBase {

	var $mProductObjects;
	var $total;
	var $weight;
	var $free_shipping_item;
	var $free_shipping_weight;
	var $free_shipping_price;
	var $contents;

	function CommerceOrderBase() {
		parent::BitBase();
		$this->mProductObjects = array();
	}

	function getProductObject( $pProductsId ) {
		if( BitBase::verifyId( $pProductsId ) ) {
			if( !isset( $this->mProductObjects[$pProductsId] ) ) {
				$this->mProductObjects[$pProductsId] = bc_get_commerce_product( zen_get_prid( $pProductsId ) );
				if( $this->mProductObjects[$pProductsId]->load() ) {
					$ret = &$this->mProductObjects[$pProductsId];
				}
			}
		}
		return $this->mProductObjects[$pProductsId];
	}

	function getWeight() {
		if( empty( $this->weight ) ) {
			$this->calculate();
		}
		return( $this->weight );
	}

}

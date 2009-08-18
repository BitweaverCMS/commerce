<?php
// +----------------------------------------------------------------------+
// | bitcommerce															|
// | Copyright (c) 2007-2009 bitcommerce.org									 |
// | http://www.bitcommerce.org											 |
// | This source file is subject to version 2.0 of the GPL license		|
// +----------------------------------------------------------------------+
/**
 * @version	$Header: /cvsroot/bitweaver/_bit_commerce/classes/CommerceOrderBase.php,v 1.1 2009/08/18 19:56:39 spiderr Exp $
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
				$this->mProductObjects[$pProductsId] = new CommerceProduct( zen_get_prid( $pProductsId ) );
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

	// calculates totals
	function calculate( $pForceRecalculate=FALSE ) {
		global $gBitDb;
		if( is_null( $this->total ) || $pForceRecalculate ) {
			$this->subtotal = 0;
			$this->total = 0;
			$this->weight = 0;

			// shipping adjustment
			$this->free_shipping_item = 0;
			$this->free_shipping_price = 0;
			$this->free_shipping_weight = 0;

			if( !is_array($this->contents) ) {
				 return 0;
			}

			reset($this->contents);
			foreach( array_keys( $this->contents ) as $productsKey ) {
				$qty = $this->contents[$productsKey]['quantity'];
				if( !empty( $this->contents[$productsKey]['products_id'] ) ) {
					// $productsKey will be orders_products_id for an order
					$prid = $this->contents[$productsKey]['products_id']; 
				} else {
					// $productsKey will be unique joined string of products_id:hash for cart, eg: 17054:be19531ba04f4dc3fd33bca49a16dca8 
					$prid = zen_get_prid( $productsKey );
				}

				// products price
				$product = $this->getProductObject( $prid );
				// sometimes 0 hash things can get stuck in cart.
				if( $product && $product->isValid() ) {
					$products_tax = zen_get_tax_rate($product->getField('products_tax_class_id'));
					$products_price = $product->getPurchasePrice( $qty, $this->contents[$productsKey]['attributes'] );
					$onetimeCharges = $product->getOneTimeCharges( $qty, $this->contents[$productsKey]['attributes'] );

					// shipping adjustments
					if (($product->getField('product_is_always_free_ship') == 1) or ($product->getField('products_virtual') == 1) or (ereg('^GIFT', addslashes($product->getField('products_model'))))) {
						$this->free_shipping_item += $qty;
						$this->free_shipping_price += zen_add_tax($products_price, $products_tax) * $qty;
						$this->free_shipping_weight += $product->getWeight( $qty, $this->contents[$productsKey]['attributes'] );
					}

					$this->total += zen_add_tax( (($products_price * $qty) + $onetimeCharges), $products_tax);
					$this->subtotal += $this->total;
					$this->weight += $product->getWeight( $qty, $this->contents[$productsKey]['attributes'] );
				}
			}
		}
	}

}

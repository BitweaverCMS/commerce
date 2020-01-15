<?php
/**
 * @package bitcommerce
 * @author spiderr <spiderr@bitweaver.org>
 * Copyright (c) 2020 bitweaver.org, All Rights Reserved
 * This source file is subject to the 2.0 GNU GENERAL PUBLIC LICENSE. 
 *
 * Base class for all plugin types.
 *
 */

abstract class CommerceBase extends BitBase {

	protected function getCommerceConfig( $pConfigKey, $pDefaultValue=NULL ) {
		global $gCommerceSystem;
		return $gCommerceSystem->getConfig( $pConfigKey, $pDefaultValue );
	}

	protected function isConfigActive( $pConfigName ) {
		global $gCommerceSystem;
		return $gCommerceSystem->isConfigActive( $pConfigName );
	}

	protected function isConfigLoaded( $pConfigName ) {
		global $gCommerceSystem;
		return $gCommerceSystem->isConfigLoaded( $pConfigName );
	}

	protected function storeConfig ( $pConfigKey, $pConfigValue ) {
		global $gCommerceSystem;
		return $gCommerceSystem->storeConfig( $pConfigKey, $pConfigValue );
	}


}

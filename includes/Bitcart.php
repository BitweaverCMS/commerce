<?php
require_once( BITCOMMERCE_PKG_PATH.'includes/bitcommerce_start_inc.php' );
require_once( BITCOMMERCE_PKG_PATH.'includes/functions/functions_general.php' );

class Bitcart
{
	function manufacturerExists( $pManufacturersId ) {
		global $gBitDb;
		$ret = NULL;
		if( is_numeric( $pManufacturersId ) ) {
			$sql = "SELECT COUNT(*) as `total`
					FROM " . TABLE_MANUFACTURERS . "
					WHERE `manufacturers_id` = '" . zen_db_input( $pManufacturersId ) . "'";
			$rs = $gBitDb->Execute($sql);
			$ret = !empty( $rs['fields']['total'] );
		}
		return( $ret );
	}

	function storeManufacturer( &$pParamHash ) {
	
		$sql_data_array = array('manufacturers_name' => zen_db_prepare_input($pParamHash['manufacturers_name']) );
		$sql_data_array['manufacturers_image'] = !empty( $pParamHash['manufacturers_image'] ) ? $pParamHash['manufacturers_image'] : NULL;
		
		if( !empty( $pParamHash['manufacturers_id'] ) && $this->manufacturerExists( $pParamHash['manufacturers_id'] ) ) {
			$sql_data_array['last_modified'] = $gBitDb->NOW();
			$manufacturers_id = zen_db_prepare_input($pParamHash['manufacturers_id']);
			$gBitDb->associateInsert(TABLE_MANUFACTURERS, $sql_data_array, 'update', "manufacturers_id = '" . (int)$manufacturers_id . "'");
		} else {
			if( !empty( $pParamHash['manufacturers_id'] ) ) {
				$sql_data_array['manufacturers_id'] = $pParamHash['manufacturers_id'];
			}
			$sql_data_array['date_added'] = $gBitDb->NOW();
			$gBitDb->associateInsert(TABLE_MANUFACTURERS, $sql_data_array);
			if( !empty( $pParamHash['manufacturers_id'] ) ) {
				$sql_data_array['manufacturers_id'] = $pParamHash['manufacturers_id'];
			}
		}

		$languages = zen_get_languages();

		for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
			$manufacturers_url_array = $pParamHash['manufacturers_url'];
			$language_id = $languages[$i]['id'];
			$sql_data_array = array('manufacturers_url' => zen_db_prepare_input($manufacturers_url_array[$language_id]));

			if ($action == 'insert') {
				$insert_sql_data = array('manufacturers_id' => $manufacturers_id, 'languages_id' => $language_id);
				$sql_data_array = array_merge($sql_data_array, $insert_sql_data);
				$gBitDb->associateInsert(TABLE_MANUFACTURERS_INFO, $sql_data_array);
			} elseif ($action == 'save') {
				$gBitDb->associateInsert(TABLE_MANUFACTURERS_INFO, $sql_data_array, 'update', "manufacturers_id = '" . (int)$manufacturers_id . "' and languages_id = '" . (int)$language_id . "'");
			}
		}
	}
}
global $gBitcart;
$gBitcart = new Bitcart();
?>
